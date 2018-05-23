<?php

namespace Anakeen;

use Anakeen\Core\DocManager\Exception;
use Anakeen\Core\Internal\DocumentAccess;

class SmartElementManager extends \Anakeen\Core\SEManager
{

    public static function getDocument($documentIdentifier, $latest = true, $useCache = true)
    {
        $doc = parent::getDocument($documentIdentifier, $latest, $useCache);
        if ($doc) {
            $doc->disableAccessControl(false);
            $err=$doc->control("view");
            if ($err) {
                throw new Exception($err);
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


}