<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Errors code used by document manager class
 * @class ErrorCodeDM
 * @see   ErrorCode
 * @brief List all error code document manager errors
 * @see   ErrorCode
 */
class ErrorCodeAPIDM
{
    /**
     * @errorCode The family used to create a document not exists
     * @see       \Anakeen\Core\SEManager::createDocument
     * @see       \Anakeen\Core\SEManager::createtemporaryDocument
     */
    const APIDM0001 = 'Family identifier "%s" not exists';
    /**
     * @errorCode The family used to create a document not exists
     * @see       \Anakeen\Core\SEManager::createDocument
     * @see       \Anakeen\Core\SEManager::createtemporaryDocument
     */
    const APIDM0002 = 'Family "%s" (#%d) not exists';
    /**
     * @errorCode Acl "create" is needed to create document
     * @see       \Anakeen\Core\SEManager::createDocument
     */
    const APIDM0003 = 'Family "%s" : no permission to create document of its family';
    /**
     * @errorCode The document must be instancied before
     * @see       \Anakeen\Core\SEManager::getDocumentFromRawDocument
     */
    const APIDM0004 = 'Convertion aborted : no identificator detected : %s';
    /**
     * @errorCode The document must have a family
     * @see       \Anakeen\Core\SEManager::getDocumentFromRawDocument
     */
    const APIDM0005 = 'Convertion aborted : no family detected : %s';
    /**
     * @errorCode Argument must be an integer
     * @see       \Anakeen\Core\SEManager::getLatestDocId
     */
    const APIDM0100 = 'Internal error : id must be numeric "%s"';
    /**
     * @errorCode Argument must be an alphanum
     * @see       \Anakeen\Core\SEManager::getIdFromName
     */
    const APIDM0101 = 'Logical name syntax: not alphanum string "%s"';
    /**
     * @errorCode Family idnetifier not an number
     * @see       \Anakeen\Core\SEManager::requireFamilyClass
     */
    const APIDM0102 = 'Family Identifier "%s" must be a number';
    /**
     * @errorCode Only affected documents can be set to cache
     * @see       \Anakeen\Core\DocManager\Cache::addDocument
     */
    const APIDM0200 = 'Cannot set to cache a document without identifier';
    /**
     * @errorCode The cache not accept to reference document
     * @see       \Anakeen\Core\DocManager\Cache::addDocument
     */
    const APIDM0201 = 'Cannot set to cache the document "%s" (#%d)';
    /**
     * @errorCode The cache not accept to clear all references
     * @see       \Anakeen\Core\DocManager\Cache::clear
     */
    const APIDM0202 = 'Cannot clear document cache';
    /**
     * @errorCode The cache not accept to clear all references
     * @see       \Anakeen\Core\DocManager\Cache::isDocumentIdInCache
     */
    const APIDM0203 = 'Identifier must be a number';

    /**
     * @errorCode
     * for beautifier
     */
    private function _bo()
    {
        if (true) {
            return;
        }
    }
}

