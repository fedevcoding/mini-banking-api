<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Account;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BalanceController
{
    use JsonResponse;

    // -------------------------------------------------------------------------
    // GET /accounts/{id}/balance
    // -------------------------------------------------------------------------
    public function balance(Request $request, Response $response, array $args): Response
    {
        $account = Account::find((int) $args['id']);
        if (!$account) {
            return $this->error($response, 'Account not found', 404);
        }

        $balance = $account->getBalance();

        return $this->json($response, [
            'account_id' => $account->id,
            'owner_name' => $account->owner_name,
            'currency' => $account->currency,
            'balance' => $balance,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /accounts/{id}/balance/convert/fiat?to=USD
    // -------------------------------------------------------------------------
    public function convertFiat(Request $request, Response $response, array $args): Response
    {
        $account = Account::find((int) $args['id']);
        if (!$account) {
            return $this->error($response, 'Account not found', 404);
        }

        $params = $request->getQueryParams();
        $to = strtoupper(trim($params['to'] ?? ''));

        if ($to === '') {
            return $this->error($response, 'Missing target currency (to)', 400);
        }

        $from = strtoupper($account->currency);
        $balance = $account->getBalance();

        // Call Frankfurter
        $url = "https://api.frankfurter.dev/v1/latest?base={$from}&symbols={$to}";
        $json = @file_get_contents($url);

        if ($json === false) {
            return $this->error($response, 'External exchange API unavailable', 502);
        }

        $data = json_decode($json, true);

        if (!isset($data['rates'][$to])) {
            return $this->error($response, "Target currency '{$to}' not supported", 400);
        }

        $rate = (float) $data['rates'][$to];
        $converted = round($balance * $rate, 2);

        return $this->json($response, [
            'account_id' => $account->id,
            'provider' => 'Frankfurter',
            'conversion_type' => 'fiat',
            'from_currency' => $from,
            'to_currency' => $to,
            'original_balance' => $balance,
            'rate' => $rate,
            'converted_balance' => $converted,
            'date' => $data['date'] ?? null,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /accounts/{id}/balance/convert/crypto?to=BTC
    // -------------------------------------------------------------------------
    public function convertCrypto(Request $request, Response $response, array $args): Response
    {
        $account = Account::find((int) $args['id']);
        if (!$account) {
            return $this->error($response, 'Account not found', 404);
        }

        $params = $request->getQueryParams();
        $crypto = strtoupper(trim($params['to'] ?? ''));

        if ($crypto === '') {
            return $this->error($response, 'Missing target crypto (to)', 400);
        }

        $currency = strtoupper($account->currency);
        $marketSymbol = $crypto . $currency;
        $balance = $account->getBalance();

        $exchangeInfoUrl = 'https://api.binance.com/api/v3/exchangeInfo';
        $exchangeInfoJson = @file_get_contents($exchangeInfoUrl);

        if ($exchangeInfoJson === false) {
            return $this->error($response, 'Binance API unavailable', 502);
        }

        $exchangeInfo = json_decode($exchangeInfoJson, true);

        $symbolInfo = null;
        foreach (($exchangeInfo['symbols'] ?? []) as $sym) {
            if ($sym['symbol'] === $marketSymbol) {
                $symbolInfo = $sym;
                break;
            }
        }

        if ($symbolInfo === null) {
            return $this->error(
                $response,
                "Market pair '{$marketSymbol}' does not exist on Binance",
                400
            );
        }

        if ($symbolInfo['status'] !== 'TRADING') {
            return $this->error(
                $response,
                "Market pair '{$marketSymbol}' is not currently active",
                400
            );
        }

        $priceUrl = "https://api.binance.com/api/v3/ticker/price?symbol={$marketSymbol}";
        $priceJson = @file_get_contents($priceUrl);

        if ($priceJson === false) {
            return $this->error($response, 'Binance price endpoint unavailable', 502);
        }

        $priceData = json_decode($priceJson, true);

        if (!isset($priceData['price'])) {
            return $this->error($response, 'Could not retrieve price from Binance', 502);
        }

        $price = (float) $priceData['price'];
        $convertedAmount = $price > 0 ? round($balance / $price, 8) : 0.0;

        return $this->json($response, [
            'account_id' => $account->id,
            'provider' => 'Binance',
            'conversion_type' => 'crypto',
            'from_currency' => $currency,
            'to_crypto' => $crypto,
            'market_symbol' => $marketSymbol,
            'original_balance' => $balance,
            'price' => $price,
            'converted_amount' => $convertedAmount,
        ]);
    }
}