<?php

namespace Anakeen\SmartStructures\Cvdoc;

use Anakeen\Core\Internal\Autoloader;
use Anakeen\Ui\IRenderConfig;
use Anakeen\Ui\IRenderConfigAccess;

class CVDocConstraint
{
    protected static function cvIsA($className, $isA)
    {
        if ($className) {
            try {
                if ($className[0] !== '\\') {
                    $className = '\\' . $className;
                }
                if ($isA[0] !== '\\') {
                    $isA = '\\' . $isA;
                }

                if (!Autoloader::classExists($className)) {
                    return sprintf(___("Class \"%s\" not exists", "smartCvdoc"), $className);
                }


                $a = new $className();
                if (!is_a($a, $isA)) {
                    return sprintf(___("Class \"%s\" not implement %s", "smartCvdoc"), $className, $isA);
                }
            } catch (\Exception $e) {
                return sprintf(___("Class \"%s\" problem : %s", "smartCvdoc"), $className, $e->getMessage());
            }
        }
        return "";
    }

    public static function isARenderConfig($className)
    {
        return self::cvIsA($className, IRenderConfig::class);
    }
    public static function isARenderAccess($className)
    {
        return self::cvIsA($className, IRenderConfigAccess::class);
    }
}
