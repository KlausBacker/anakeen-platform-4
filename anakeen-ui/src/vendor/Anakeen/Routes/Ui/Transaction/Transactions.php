<?php


namespace Anakeen\Routes\Ui\Transaction;


use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

class Transactions
{
    protected $transaction = null;


    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $method = $request->getMethod();
        switch ($method) {
            case "GET":
                $data = $this->onReadRequest($request, $response, $args);
                return ApiV2Response::withData($response, $data);
            case "POST":
                $transaction = TransactionManager::createTransaction();
                return ApiV2Response::withData($response, $transaction->getData());
            default:
                $exception = new Exception("TRANS0001", $method);
                $exception->setHttpStatus("405", "Method not supported");
                throw $exception;
        }
    }

    public function doTransactionAction($transactionId) {
        sleep(20);
        TransactionManager::updateProgression($transactionId,  [
            "progress" => "Oulà ça fait 20 secondes"
        ]);
        sleep(20);
        TransactionManager::updateProgression($transactionId,  [
            "progress" => "Oulà maintenant ça fait 40 secondes"
        ]);
        sleep(5);
        return "Cette fois j'ai terminé";
    }

    protected function onReadRequest(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $requestTransId = $args["transactionId"];
        if (empty($requestTransId)) {
            $exception = new Exception("TRANS0002");
            $exception->setHttpStatus("400", "Transaction id missing");
            throw $exception;
        }

        $dataResponse = TransactionManager::runTransaction($requestTransId, [$this, "doTransactionAction"]);

        return $dataResponse;
    }
}