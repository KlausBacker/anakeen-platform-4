<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace {
    /**
     * Errors code used by document manager class
     * @class ErrorCodeDM
     * @see ErrorCode
     * @brief List all error code document manager errors
     * @see ErrorCode
     */
    class ErrorCodeAPIDM
    {
        /**
         * @errorCode The family used to create a document not exists
         * @see \Dcp\Core\DocManager::createDocument
         * @see \Dcp\Core\DocManager::createtemporaryDocument
         */
        const APIDM0001 = 'Family identifier "%s" not exists';
        /**
         * @errorCode The family used to create a document not exists
         * @see \Dcp\Core\DocManager::createDocument
         * @see \Dcp\Core\DocManager::createtemporaryDocument
         */
        const APIDM0002 = 'Family "%s" (#%d) not exists';
        /**
         * @errorCode Acl "create" is needed to create document
         * @see \Dcp\Core\DocManager::createDocument
         */
        const APIDM0003 = 'Family "%s" : no permission to create document of its family';
        /**
         * @errorCode The document must be instancied before
         * @see \Dcp\Core\DocManager::getDocumentFromRawDocument
         */
        const APIDM0004 = 'Convertion aborted : no identificator detected : %s';
        /**
         * @errorCode The document must have a family
         * @see \Dcp\Core\DocManager::getDocumentFromRawDocument
         */
        const APIDM0005 = 'Convertion aborted : no family detected : %s';
        /**
         * @errorCode Argument must be an integer
         * @see \Dcp\Core\DocManager::getLatestDocId
         */
        const APIDM0100 = 'Internal error : id must be numeric "%s"';
        /**
         * @errorCode Argument must be an alphanum
         * @see \Dcp\Core\DocManager::getIdFromName
         */
        const APIDM0101 = 'Logical name syntax: not alphanum string "%s"';
        /**
         * @errorCode Family idnetifier not an number
         * @see \Dcp\Core\DocManager::requireFamilyClass
         */
        const APIDM0102 = 'Family Identifier "%s" must be a number';
        /**
         * @errorCode Only affected documents can be set to cache
         * @see \Dcp\Core\DocManager\Cache::addDocument
         */
        const APIDM0200 = 'Cannot set to cache a document without identifier';
        /**
         * @errorCode The cache not accept to reference document
         * @see \Dcp\Core\DocManager\Cache::addDocument
         */
        const APIDM0201 = 'Cannot set to cache the document "%s" (#%d)';
        /**
         * @errorCode The cache not accept to clear all references
         * @see \Dcp\Core\DocManager\Cache::clear
         */
        const APIDM0202 = 'Cannot clear document cache';
        /**
         * @errorCode The cache not accept to clear all references
         * @see \Dcp\Core\DocManager\Cache::isDocumentIdInCache
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
}
namespace Dcp\Core\DocManager {
    class Exception extends \Dcp\Exception
    {
    }
}
