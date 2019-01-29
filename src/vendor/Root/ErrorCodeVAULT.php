<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used when Vault Manager
 * @class ErrorCodeVAULT
 * @brief List all error code for Vault Manager
 * @see Anakeen\Core\VaultManager
 */
class ErrorCodeVAULT
{
    /**
     * @errorCode File cannot be stored to vault
     * @see Anakeen\Core\VaultManager::storeFile
     */
    const VAULT0001 = 'Cannot store file "%s" : %s';
    /**
     * @errorCode Temporary file cannot be stored to vault
     * @see Anakeen\Core\VaultManager::storeTemporaryFile
     */
    const VAULT0002 = 'Cannot store temporary file : %s';
    /**
     * @errorCode Temporary file cannot be stored to vault because user's session not found
     * @see Anakeen\Core\VaultManager::storeTemporaryFile
     */
    const VAULT0003 = 'Cannot store temporary file : no session detected';
    /**
     * @errorCode File cannot be destroyed
     * @see Anakeen\Core\VaultManager::destroyFile
     */
    const VAULT0004 = 'Cannot destroy file : %s';
}
