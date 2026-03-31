<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TransactionController
{
    use JsonResponse;

    // -------------------------------------------------------------------------
    // GET /accounts/{id}/transactions
    // -------------------------------------------------------------------------
    public function index(Request $request, Response $response, array $args): Response
    {
        $account = Account::find((int) $args['id']);
        if (!$account) {
            return $this->error($response, 'Account not found', 404);
        }

        $transactions = $account->transactions()
            ->orderBy('created_at', 'desc')
            ->get(['id', 'account_id', 'type', 'amount', 'description', 'balance_after', 'created_at']);

        return $this->json($response, [
            'account_id' => $account->id,
            'transactions' => $transactions,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /accounts/{id}/transactions/{txId}
    // -------------------------------------------------------------------------
    public function show(Request $request, Response $response, array $args): Response
    {
        $account = Account::find((int) $args['id']);
        if (!$account) {
            return $this->error($response, 'Account not found', 404);
        }

        $tx = Transaction::where('account_id', $account->id)
            ->find((int) $args['txId']);

        if (!$tx) {
            return $this->error($response, 'Transaction not found', 404);
        }

        return $this->json($response, $tx);
    }

    // -------------------------------------------------------------------------
    // POST /accounts/{id}/deposits
    // -------------------------------------------------------------------------
    public function deposit(Request $request, Response $response, array $args): Response
    {
        $account = Account::find((int) $args['id']);
        if (!$account) {
            return $this->error($response, 'Account not found', 404);
        }

        $body = (array) $request->getParsedBody();

        if (empty($body['amount'])) {
            return $this->error($response, 'Missing amount', 400);
        }

        $amount = (float) $body['amount'];
        if ($amount <= 0) {
            return $this->error($response, 'Amount must be greater than zero', 400);
        }

        $description = trim((string) ($body['description'] ?? ''));

        $balanceBefore = $account->getBalance();
        $balanceAfter = $balanceBefore + $amount;

        $tx = Transaction::create([
            'account_id' => $account->id,
            'type' => 'deposit',
            'amount' => $amount,
            'description' => $description,
            'balance_after' => $balanceAfter,
        ]);

        return $this->json($response, [
            'message' => 'Deposit registered',
            'transaction' => $tx,
            'balance_after' => $balanceAfter,
        ], 201);
    }

    // -------------------------------------------------------------------------
    // POST /accounts/{id}/withdrawals
    // -------------------------------------------------------------------------
    public function withdrawal(Request $request, Response $response, array $args): Response
    {
        $account = Account::find((int) $args['id']);
        if (!$account) {
            return $this->error($response, 'Account not found', 404);
        }

        $body = (array) $request->getParsedBody();

        if (empty($body['amount'])) {
            return $this->error($response, 'Missing amount', 400);
        }

        $amount = (float) $body['amount'];
        if ($amount <= 0) {
            return $this->error($response, 'Amount must be greater than zero', 400);
        }

        $description = trim((string) ($body['description'] ?? ''));
        $currentBalance = $account->getBalance();

        if ($amount > $currentBalance) {
            return $this->error($response, 'Insufficient funds', 422);
        }

        $balanceAfter = $currentBalance - $amount;

        $tx = Transaction::create([
            'account_id' => $account->id,
            'type' => 'withdrawal',
            'amount' => $amount,
            'description' => $description,
            'balance_after' => $balanceAfter,
        ]);

        return $this->json($response, [
            'message' => 'Withdrawal registered',
            'transaction' => $tx,
            'balance_after' => $balanceAfter,
        ], 201);
    }

    // -------------------------------------------------------------------------
    // PUT /accounts/{id}/transactions/{txId}  — only description is editable
    // -------------------------------------------------------------------------
    public function update(Request $request, Response $response, array $args): Response
    {
        $account = Account::find((int) $args['id']);
        if (!$account) {
            return $this->error($response, 'Account not found', 404);
        }

        $tx = Transaction::where('account_id', $account->id)
            ->find((int) $args['txId']);

        if (!$tx) {
            return $this->error($response, 'Transaction not found', 404);
        }

        $body = (array) $request->getParsedBody();

        if (!isset($body['description'])) {
            return $this->error($response, 'Missing description field', 400);
        }

        $tx->description = trim((string) $body['description']);
        $tx->save();

        return $this->json($response, [
            'message' => 'Transaction updated',
            'transaction' => $tx,
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE /accounts/{id}/transactions/{txId}
    // Rule: only the most recent transaction of the account can be deleted.
    // -------------------------------------------------------------------------
    public function destroy(Request $request, Response $response, array $args): Response
    {
        $account = Account::find((int) $args['id']);
        if (!$account) {
            return $this->error($response, 'Account not found', 404);
        }

        $tx = Transaction::where('account_id', $account->id)
            ->find((int) $args['txId']);

        if (!$tx) {
            return $this->error($response, 'Transaction not found', 404);
        }

        // Only the last transaction can be deleted
        $lastTx = $account->transactions()->latest()->first();
        if ($lastTx->id !== $tx->id) {
            return $this->error($response, 'Only the most recent transaction can be deleted', 422);
        }

        $tx->delete();

        return $this->json($response, ['message' => 'Transaction deleted']);
    }
}