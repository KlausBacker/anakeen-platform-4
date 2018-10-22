<?php

/**
 * Errors code used
 * @class ErrorCodeTransaction
 * @brief List all error code errors
 * @see   ErrorCodeTransaction
 */
class ErrorCodeTRANS
{
    /**
     * @errorCode in case of method is not valid
     */
    const TRANS0001 = 'Invalid method %s';

    /**
     * @errorCode in case of read a transaction without provide the transaction id
     */
    const TRANS0002 = 'The transaction id is needed to launch the transaction';

    /**
     * @errorCode in case of set a bad transaction status
     */
    const TRANS0003 = 'The transaction status %s is not valid';

    /**
     * @errorCode in case of the transaction cannot be started
     */
    const TRANS0004 = 'The transaction %s cannot be started because it is in %s status';

    /**
     * @errorCode in case of the transaction cannot be stored
     */
    const TRANS0005 = 'The transaction %s cannot be stored because a transaction with the same id already exist';
}
