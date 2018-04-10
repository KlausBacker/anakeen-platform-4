<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\SmartStructures\Cvdoc;

use Anakeen\Core\Internal\Autoloader;
use \SmartStructure\Attributes\Cvdoc as MyAttributes;

class CVDocHooks extends CoreCVDoc
{
    public static function cvIsA($className, $isA)
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
                    return sprintf(___("Class \"%s\" not exists"), $className);
                }


                $a = new $className();
                if (!is_a($a, $isA)) {
                    return sprintf(___("Class \"%s\" not implement %s"), $className, $isA);
                }
            } catch (\Exception $e) {
                return sprintf(___("Class \"%s\" problem : %s"), $className, $e->getMessage());
            }
        }
        return "";
    }
}
