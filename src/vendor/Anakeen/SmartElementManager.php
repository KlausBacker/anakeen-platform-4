<?php

namespace Anakeen;

use Anakeen\Router\Exception;
use Anakeen\Core\Internal\DocumentAccess;

class SmartElementManager extends \Anakeen\Core\SEManager
{

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


    public static function createDocument($familyIdentifier, $useDefaultValues = true)
    {
        $doc = parent::createDocument($familyIdentifier, $useDefaultValues);
        $family = $doc->getFamilyDocument();
        $err = $family->control('create');
        if ($err != "") {
            throw new Exception("APIDM0003", $familyIdentifier);
        }
        $doc->disableAccessControl(false);
        return $doc;
    }
}
