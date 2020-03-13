<?php

namespace Anakeen;

use Anakeen\Core\Internal\DocumentAccess;

class SmartElementManager extends \Anakeen\Core\SEManager
{
    /**
     * @param int|string $documentIdentifier
     * @param bool       $latest
     * @param bool       $useCache
     * @return Core\Internal\SmartElement | null
     * @throws Core\DocManager\Exception
     * @throws Exception
     */
    public static function getDocument($documentIdentifier, $latest = true, $useCache = true)
    {
        $doc = parent::getDocument($documentIdentifier, $latest, $useCache);
        if ($doc) {
            $doc->disableAccessControl(false);
            $err = $doc->control("view");
            if ($err) {
                $exception = new Exception($err);
                $exception->setHttpStatus("403", "Forbidden");
                throw $exception;
            }
        }
        return $doc;
    }

    public static function getRawDocument($documentIdentifier, $latest = true)
    {
        $doc = parent::getRawDocument($documentIdentifier, $latest);
        if ($doc) {
            if (!DocumentAccess::hasProfilControl($doc["profid"], "view")) {
                throw new Exception("APIDM0204");
            }
        }
        return $doc;
    }


    /**
     * The Smart Element is under access control
     * @param int|string $structureIdentifier
     * @param bool $useDefaultValues
     * @return Core\Internal\SmartElement
     * @throws Exception
     */
    public static function createDocument($structureIdentifier, $useDefaultValues = true)
    {
        $doc = parent::createDocument($structureIdentifier, $useDefaultValues);
        $family = $doc->getFamilyDocument();
        $err = $family->control('create');
        if ($err != "") {
            throw new Exception("APIDM0003", $structureIdentifier);
        }
        $doc->disableAccessControl(false);
        return $doc;
    }
}
