<?php


use Anakeen\Core\DbManager;

/**
 * Errors code used to database query errors
 *
 * @class ErrorCodeDB
 * @see   ErrorCode
 * @brief List all error code database errors
 * @see   ErrorCode
 */
class ErrorCodeDB
{
    /**
     * @errorCode
     * the query cannot be executed
     */
    const DB0001 = 'query error : %s';
    /**
     * @errorCode
     * the query cannot be executed after prepare
     */
    const DB0002 = 'query error : %s';
    /**
     * @errorCode
     * when try to create automatically DbObj Table
     * the sqlcreate attribute if probably wrong
     */
    const DB0003 = 'Table "%s" doesn\'t exist and cannot be created : %s';
    /**
     * @errorCode
     * when try to create automatically DbObj Table
     * the sqlcreate attribute if probably wrong
     */
    const DB0004 = 'Table "%s" cannot be updated : %s';
    /**
     * @errorCode
     * the query cannot be prepared
     */
    const DB0005 = 'query prepare error : %s';
    /**
     * @errorCode
     * the prepare statement cannot be done
     */
    const DB0006 = 'preparing statement : %s';
    /**
     * @errorCode
     * the execute statement cannot be done
     */
    const DB0007 = 'execute statement : %s';
    /**
     * @errorCode
     * the query cannot be sent to server
     */
    const DB0008 = 'sending query : %s';
    /**
     * @errorCode missing column on table
     */
    const DB0009 = 'no auto update for "%s" table';
    /**
     * @errorCode The lock prefix is converted to a 4 bytes numbre and it is limited to 4 characters
     * @see       DbManager::lockPoint()
     */
    const DB0010 = 'The prefix lock "%s" must not exceed 4 characters';
    /**
     * @errorCode Lock is efficient only into a transaction
     * @see       DbManager::lockPoint()
     */
    const DB0011 = 'The lock "%d-%s" must be set inside a savePoint transaction';
    /**
     * @errorCode Lock identifier is not a valid int32
     * @see       DbManager::lockPoint()
     */
    const DB0012 = 'Lock identifier (%s) is not a valid int32';
    /**
     * @errorCode
     * simple query error
     */
    const DB0100 = 'simple query error "%s" for query "%s"';
    /**
     * @errorCode
     * database connection error
     */
    const DB0101 = 'cannot connect to "%s"';
    /**
     * @errorCode
     * simple query error connect
     */
    const DB0102 = 'cannot connect to "%s". Simple query error "%s" for query "%s"';
    /**
     * @errorCode  Vault identifier key cannot be generated
     * @see        VaultDiskStorage::getNewVaultId
     */
    const DB0103 = 'Cannot generate vault identifier';
    /**
     * @errorCode  Vault identifier key must be verify if not already in use
     * @see        VaultDiskStorage::getNewVaultId
     */
    const DB0104 = 'Cannot verify vault identifier : %s';
    /**
     * @errorCode pg array syntax error
     */
    const DB0200 = 'malformed postgresql array literal : must begins and ends by braces "%s"';
    /**
     * @errorCode pg array syntax error
     */
    const DB0201 = 'malformed postgresql array literal : extra characters after end quote "%s"';
    /**
     * @errorCode pg array syntax error
     */
    const DB0202 = 'malformed postgresql array literal : extra characters before begin quote "%s"';
    /**
     * @errorCode pg array syntax error
     */
    const DB0203 = 'malformed postgresql array literal : missing end quote "%s"';
    /**
     * @errorCode pg array syntax error
     */
    const DB0204 = 'malformed postgresql array literal : unbalanced braces "%s"';
    /**
     * @errorCode pg array syntax error
     */
    const DB0205 = 'malformed postgresql array literal : extra characters after brace "%s"';
    /**
     * @errorCode pg array syntax error
     */
    const DB0206 = 'malformed postgresql array literal : mismatch dimension "%s"';
    /**
     * @errorCode pg array syntax error
     */
    const DB0207 = 'malformed postgresql array literal : dimension limited to 2 "%s"';
    /**
     * @errorCode pg array syntax error
     */
    const DB0208 = 'malformed postgresql array literal : empty value "%s"';
    /**
     * @errorCode pg array syntax error
     */
    const DB0209 = 'malformed postgresql array literal : invalid null char in "%s"';

}
