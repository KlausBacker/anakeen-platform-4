<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

use \Dcp\AttributeIdentifiers\Cvdoc as MyAttributes;
class CVDoc extends \Dcp\Core\CVDoc
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
                if (!\Dcp\Autoloader::classExists($className)) {
                    return sprintf(___("Class \"%s\" not exists") , $className);
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
        return "";
    }
    
    public function preConsultation()
    {
        $err = parent::preConsultation();
        $oa = $this->getAttribute(MyAttributes::cv_t_views);
        if ($oa) {
            $oa->setOption("rowviewzone", "DOCUMENT:CVDOCUI_ARRAY_VIEW");
        }
        
        $zones = $this->getMultipleRawValues(MyAttributes::cv_zview);
        $renders = $this->getMultipleRawValues(MyAttributes::cv_renderconfigclass);
        foreach ($zones as $k => $zone) {
            if ($zone && $zone !== $this->defaultview && $zone != $this->defaultedit && !$renders[$k]) {
                $this->setValue(MyAttributes::cv_renderconfigclass, sprintf("<%s>", ___("Ignored HTML5 view", "ddui")) , $k);
            } elseif ($renders[$k] && !$zone) {
                if ($renders[$k][0] === "\\") {
                    $renders[$k] = substr($renders[$k], 1);
                }
                $renders[$k] = mb_strtolower($renders[$k]);
                if ($renders[$k] !== "dcp\\ui\\defaultview" && $renders[$k] !== "dcp\\ui\\defaultedit") {
                    $this->setValue(MyAttributes::cv_zview, sprintf("<%s>", ___("Ignored CORE view", "ddui")) , $k);
                }
            }
        }
        
        return $err;
    }
    public function preEdition()
    {
        $err = parent::preEdition();
        $oa = $this->getAttribute(MyAttributes::cv_t_views);
        if ($oa) {
            $oa->setOption("roweditzone", "DOCUMENT:CVDOCUI_ARRAY_VIEW");
        }
        
        return $err;
    }
    
    public function getDisplayableViews($html5mode = false)
    {
        $views = parent::getDisplayableViews();
        foreach ($views as $k => $view) {
            $zone = $view[MyAttributes::cv_zview];
            $render = $view[MyAttributes::cv_renderconfigclass];
            
            if ($html5mode) {
                if ($zone && $zone !== $this->defaultview && $zone != $this->defaultedit && !$render) {
                    // No display special CORE zone
                    unset($views[$k]);
                }
            } else {
                if ($render) {
                    if ($render[0] === "\\") {
                        $render = substr($render, 1);
                    }
                    $render = mb_strtolower($render);
                    if (!$zone && $render !== "dcp\\ui\\defaultview" && $render !== "dcp\\ui\\defaultedit") {
                        // No display special DDUI render
                        unset($views[$k]);
                    }
                }
            }
        }
        return $views;
    }
}
