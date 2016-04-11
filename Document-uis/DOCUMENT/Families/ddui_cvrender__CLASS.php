<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class CvRender extends \Dcp\Family\Cvdoc
{
    
    public function cvIsA($className, $isA)
    {
        if ($className) {
            try {
                if ($className[0] !== '\\') {
                    $className = '\\' . $className;
                }
                if ($isA[0] !== '\\') {
                    $isA = '\\' . $isA;
                }
                $a = new $className();
                if (!is_a($a, $isA)) {
                    return sprintf(___("Class \"%s\" not implement %s") , $className, $isA);
                }
            }
            catch(\Exception $e) {
                return sprintf(___("Class \"%s\" problem : %s") , $className, $e->getMessage());
            }
        }
    }
}
