<?php


namespace Anakeen\Routes\Ui\Transaction;


use Anakeen\Router\ApiV2Response;

class PollTransaction
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args) {
        $transactionId = $args["transactionId"];
        $transactionData = TransactionManager::getTransactionData($transactionId);
        return ApiV2Response::withData($response, $transactionData);
    }
}