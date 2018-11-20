<?php

namespace Anakeen\Routes\Ui\Transaction;

/**
 * Class Transaction
 * @note Used by route : GET /api/v2/ui/transaction/{transactionId}
 * @note Used by route : POST /api/v2/ui/transaction
 * @package Anakeen\Routes\Ui
 */
class Transaction
{
    /**
      * @var string The current transaction status
      */
    protected $transactionStatus = null;

    /**
      * @var string|null The transaction unique id
      */
    protected $transactionId = null;

    /**
      * @var string|null The transaction error message
      */
    protected $transactionError = null;

    /**
      * @var array The transaction details
      */
    protected $transactionDetails = [];

    public function __construct($tId)
    {
        $this->transactionId = $tId;
        $this->transactionStatus = TransactionManager::TRANSACTION_CREATED;
    }

    /**
      * @return string
      */
    public function getStatus(): string
    {
        return $this->transactionStatus;
    }

    /**
      * @param string $transactionStatus
      */
    public function setStatus(string $transactionStatus)
    {
        $this->transactionStatus = $transactionStatus;
    }

    /**
      * @return null|string
      */
    public function getId(): string
    {
        return $this->transactionId;
    }

    /**
      * @param null|string $transactionId
      */
    public function setId(string $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
      * @return null|string
      */
    public function getError(): string
    {
        return $this->transactionError;
    }

    /**
      * @param null|string $transactionError
      */
    public function setError($transactionError)
    {
        $this->transactionError = $transactionError;
    }

    /**
      * @return array
      */
    public function getDetails(): array
    {
        return $this->transactionDetails;
    }

    /**
      * @param array $transactionDetails
      */
    public function setDetails(array $transactionDetails)
    {
        $this->transactionDetails = $transactionDetails;
    }
     
     

    public function getData()
    {
        return [
            "transactionId" => $this->transactionId,
            "transactionStatus" => $this->transactionStatus,
            "transactionError" => $this->transactionError,
            "details" => $this->transactionDetails
        ];
    }

    /**
      * @param $data - The data of the transaction
      * @return Transaction - A transaction
      */
    public static function fromData($data)
    {
        $transaction = new Transaction($data["transactionId"]);
        $transaction->setStatus($data["transactionStatus"]);
        $transaction->setError($data["transactionError"]);
        $transaction->setDetails($data["details"]);
        return $transaction;
    }
}
