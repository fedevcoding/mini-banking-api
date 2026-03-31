<?php

declare(strict_types=1);

use App\Controllers\BalanceController;
use App\Controllers\TransactionController;
use Slim\App;

return function (App $app): void {

    // Transactions
    $app->get(
        '/accounts/{id}/transactions',
        [TransactionController::class, 'index']
    );

    $app->get(
        '/accounts/{id}/transactions/{txId}',
        [TransactionController::class, 'show']
    );

    $app->post(
        '/accounts/{id}/deposits',
        [TransactionController::class, 'deposit']
    );

    $app->post(
        '/accounts/{id}/withdrawals',
        [TransactionController::class, 'withdrawal']
    );

    $app->put(
        '/accounts/{id}/transactions/{txId}',
        [TransactionController::class, 'update']
    );

    $app->delete(
        '/accounts/{id}/transactions/{txId}',
        [TransactionController::class, 'destroy']
    );

    // Balance
    $app->get(
        '/accounts/{id}/balance',
        [BalanceController::class, 'balance']
    );

    $app->get(
        '/accounts/{id}/balance/convert/fiat',
        [BalanceController::class, 'convertFiat']
    );

    $app->get(
        '/accounts/{id}/balance/convert/crypto',
        [BalanceController::class, 'convertCrypto']
    );
};