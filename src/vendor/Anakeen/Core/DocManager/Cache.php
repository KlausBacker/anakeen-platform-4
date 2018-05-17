<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Core\DocManager;

use Anakeen\Core\SmartStructure;

class Cache
{
    /**
     * @var MemoryCache $localCache
     */
    protected static $localCache = null;

    /**
     * Set document object to local cache
     *
     * Return object itself
     *
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @throws Exception APIDM0200, APIDM0201
     * @api Record document to local cache
     * @return \Anakeen\Core\Internal\SmartElement |SmartStructure
     */
    public static function &addDocument(\Anakeen\Core\Internal\SmartElement & $document)
    {
        if (empty($document->id)) {
            throw new Exception("APIDM0200");
        }
        if (($document->doctype != 'C') || (count($document->attributes->attr) > 0)) {
            if (!self::getLocalCache()->set($document->id, $document)) {
                throw new Exception("APIDM0201", $document->getTitle(), $document->id);
            }

        }

        return $document;
    }

    /**
     * Clear local cache
     *
     * @throws Exception APIDM0202
     * @api Clear local cache
     * @return void
     */
    public static function clear()
    {
        if (!self::getLocalCache()->clear()) {
            throw new Exception("APIDM0202");
        }

    }

    /**
     * Verify if object referenced by key exists
     *
     * @param int $documentId Document identifier
     * @throws Exception
     * @return bool
     */
    public static function isDocumentIdInCache($documentId)
    {
        if (empty($documentId)) {
            return false;
        }
        if (!is_numeric($documentId)) {
            throw new Exception("APIDM0203");
        }

        return self::getLocalCache()->exists($documentId);
    }

    /**
     * Return object referenced by key exists
     *
     * Return null if key not exists in cache.
     *
     * @param string $documentId object key
     * @return \Anakeen\Core\Internal\SmartElement |null
     */
    public static function getDocumentFromCache($documentId)
    {
        $cachedDocument = self::getLocalCache()->get($documentId);
        if (is_object($cachedDocument)) {
            /**
             * @var \Anakeen\Core\Internal\SmartElement $cachedDocument
             */
            return $cachedDocument;
        }
        return null;
    }

    /**
     * Unset document object from local cache
     *
     * Return removed object itself
     *
     * @param \Anakeen\Core\Internal\SmartElement $document
     *
     * @api Unset document object from local cache
     * @return \Anakeen\Core\Internal\SmartElement
     */
    public static function &removeDocument(\Anakeen\Core\Internal\SmartElement & $document)
    {
        self::getLocalCache()->remove($document->id);

        return $document;
    }

    /**
     * Unset a document's object by its id from local cache
     *
     * Return bool(true) on success or bool(false) if $key is invalid
     *
     * @param int $id
     * @return bool bool(true) on success or bool(false) if $key is invalid
     */
    public static function removeDocumentById($id)
    {
        return self::getLocalCache()->remove($id);
    }

    /**
     * Verify if document object is in cache
     *
     * Return true is object is in local cache
     *
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return bool
     */
    public static function isInCache(\Anakeen\Core\Internal\SmartElement & $document)
    {
        return self::getLocalCache()->isInCache($document->id, $document);
    }

    /**
     * Get local cache object
     * @return MemoryCache
     */
    protected static function getLocalCache()
    {
        if (self::$localCache === null) {
            self::$localCache = new MemoryCache();
        }
        return self::$localCache;
    }
}
