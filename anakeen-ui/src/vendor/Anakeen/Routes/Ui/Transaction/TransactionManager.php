<?php


namespace Anakeen\Routes\Ui\Transaction;

use Anakeen\Core\ContextManager;
use Anakeen\Router\Exception;

class TransactionManager
{
    // Transaction possible status
    const TRANSACTION_CREATED = "CREATED";
    const TRANSACTION_DONE = "DONE";
    const TRANSACTION_PENDING = "PENDING";
    const TRANSACTION_ERROR = "ERROR";

    const TRANSACTION_ID_PREFIX = "ank-tid-";

    /**
     * Create a new transaction
     * @return Transaction the new transaction
     * @throws Exception
     */
    public static function createTransaction()
    {
        $transaction = new Transaction(self::generateTransactionId());
        self::storeTransaction($transaction, false);
        return $transaction;
    }

    /**
     * @param $transactionId
     * @param callable $transactionAction
     * @param bool $autoDone - Auto done the transaction after the user callable function has finished
     * @return Transaction|null
     * @throws Exception
     */
    public static function runTransaction($transactionId, $transactionAction, $autoDone = true)
    {
        $transaction = self::loadTransaction($transactionId);
        if ($transaction->getStatus() !== self::TRANSACTION_CREATED) {
            $exception = new Exception("TRANS0004", $transaction->getId(), $transaction->getStatus());
            $exception->setHttpStatus("500", "Transaction cannot be started");
            throw $exception;
        }
        $transaction = self::changeStatus($transaction, self::TRANSACTION_PENDING);
        // The user func can take long time
        $userActionReturn = call_user_func($transactionAction, $transaction->getId());
        if ($autoDone) {
            self::transactionDone($transactionId);
        }
        return $userActionReturn;
    }

    /**
     * @param $transactionId
     * @return Transaction|null
     * @throws Exception
     */
    public static function transactionDone($transactionId)
    {
        $transaction = self::loadTransaction($transactionId);
        if ($transaction->getStatus() !== self::TRANSACTION_PENDING) {
            return $transaction;
        }
        $transaction = self::changeStatus($transactionId, self::TRANSACTION_DONE);
        return $transaction;
    }

    /**
     * @param $transactionId
     * @param $transactionError
     * @return Transaction|null
     * @throws Exception
     */
    public static function transactionError($transactionId, $transactionError)
    {
        $transaction = self::loadTransaction($transactionId);
        if ($transaction->getStatus() === self::TRANSACTION_ERROR) {
            return $transaction;
        }
        $transaction->setError($transactionError);
        $transaction = self::changeStatus($transaction, self::TRANSACTION_ERROR);
        return $transaction;
    }

    /**
     * @param $transactionId
     * @param $details
     * @return Transaction
     * @throws Exception
     */
    public static function updateProgression($transactionId, $details)
    {
        $transaction = self::loadTransaction($transactionId);
        if ($transaction->getStatus() !== self::TRANSACTION_PENDING) {
            return $transaction;
        }
        $transaction->setDetails($details);
        self::storeTransaction($transaction);
        return $transaction;
    }

    public static function getTransactionData($transactionId)
    {
        $transaction = self::loadTransaction($transactionId);
        return $transaction->getData();
    }

    /**
     * Generate a unique identifier for transaction
     * @return string - The transaction id
     */
    protected static function generateTransactionId()
    {
        return uniqid(self::TRANSACTION_ID_PREFIX);
    }

    /**
     * @param Transaction $transaction
     * @param bool $erase
     * @throws Exception
     */
    protected static function storeTransaction(Transaction $transaction, $erase = true)
    {
        if (!$erase) {
            $alreadyExist = self::loadTransaction($transaction->getId(), false);
            if ($alreadyExist !== null) {
                $exception = new Exception("TRANS0005", $transaction->getId());
                $exception->setHttpStatus("500", "Transaction cannot be stored");
                throw $exception;
            }
        }
        ContextManager::getSession()->register($transaction->getId(), json_encode($transaction->getData()));
    }

    /**
     * @param $transactionId
     * @param bool $useDefault
     * @return Transaction
     */
    protected static function loadTransaction($transactionId, $useDefault = true)
    {
        $defaultData = null;
        if ($useDefault) {
            $errorTransaction = new Transaction($transactionId);
            $errorTransaction->setStatus(self::TRANSACTION_ERROR);
            $errorTransaction->setError("The transaction id does not match any existing transaction");
            $defaultData = $errorTransaction->getData();
        }

        $storedTransactionData = json_decode(ContextManager::getSession()
            ->read(
                $transactionId,
                json_encode($defaultData)
            ), true);
        if (is_array($storedTransactionData)) {
            return Transaction::fromData($storedTransactionData);
        } else {
            return null;
        }
    }

    /**
     * @param $transactionId
     * @param $status
     * @return Transaction|null
     * @throws Exception
     */
    protected static function changeStatus($transactionId, $status)
    {
        if (!($status === self::TRANSACTION_PENDING
            || $status === self::TRANSACTION_ERROR
            || $status === self::TRANSACTION_DONE
            || $status === self::TRANSACTION_CREATED)) {
            $exception = new Exception("TRANS0003", $status);
            $exception->setHttpStatus("500", "Transaction status invalid");
            throw $exception;
        }

        $transaction = null;
        if (is_a($transactionId, "\Anakeen\Routes\Ui\Transaction\Transaction")) {
            $transaction = $transactionId;
        } elseif (is_string($transactionId)) {
            $transaction = self::loadTransaction($transactionId);
        }

        switch ($transaction->getStatus()) {
            case self::TRANSACTION_ERROR:
            case self::TRANSACTION_DONE:
                break;
            case self::TRANSACTION_CREATED:
            case self::TRANSACTION_PENDING:
                $transaction->setStatus($status);
                self::storeTransaction($transaction);
                break;
        }
        return $transaction;
    }
}
