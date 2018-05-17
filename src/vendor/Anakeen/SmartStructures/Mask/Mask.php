<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Mask document
 *
 */

namespace Anakeen\SmartStructures\Mask;

use Anakeen\Core\SEManager;
use Anakeen\SmartHooks;
use \SmartStructure\Attributes\Mask as myAttr;

class Mask extends \SmartStructure\Base
{
    public $defaultedit = "FREEDOM:EDITMASK";
    public $defaultview = "FREEDOM:VIEWMASK";

    public function getLabelVis()
    {
        return array(
            "-" => " ",
            "R" => _("read only"),
            "W" => _("read write"),
            "O" => _("write only"),
            "H" => _("hidden"),
            "S" => _("read disabled"),
            "U" => _("static array"),
            "I" => _("invisible")
        );
    }

    public function getLabelNeed()
    {
        return array(
            "-" => " ",
            "Y" => _("Y"),
            "N" => _("N")
        );
    }


    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            /**
             * suppress unmodified attributes visibilities
             * to simplify the mask structure
             */
            $tneed = $this->getMultipleRawValues("MSK_NEEDEEDS");
            $tattrid = $this->getMultipleRawValues("MSK_ATTRIDS");
            $tvis = $this->getMultipleRawValues("MSK_VISIBILITIES");

            foreach ($tattrid as $k => $v) {
                if (($tneed[$k] === '-') && ($tvis[$k] === '-') || ($tneed[$k] === '') && ($tvis[$k] === '-')) {
                    unset($tneed[$k]);
                    unset($tvis[$k]);
                    unset($tattrid[$k]);
                }
            }
            $this->setValue("MSK_NEEDEEDS", $tneed);
            $this->setValue("MSK_ATTRIDS", $tattrid);
            $this->setValue("MSK_VISIBILITIES", $tvis);

            return '';
        })->addListener(SmartHooks::PREIMPORT, function () {
            return $this->verifyIntegraty();
        });
    }




    public function preRefresh()
    {
        return $this->verifyIntegraty();
    }

    /**
     * Verify if family and attributes are coherents
     * @return string error message
     */
    public function verifyIntegraty()
    {
        $mskAttrids = $this->getMultipleRawValues(myAttr::msk_attrids);
        $famid = $this->getRawValue(myAttr::msk_famid);
        if (!$famid) {
            return \ErrorCode::getError("MSK0001", $this->name);
        }
        $fam = SEManager::getFamily($famid);
        if (!$fam || $fam->doctype !== "C") {
            return \ErrorCode::getError("MSK0002", $famid, $this->name);
        }
        $attributes = $fam->getAttributes();
        $attrids = [];
        foreach ($attributes as $attribute) {
            if ($attribute->usefor !== "Q") {
                $attrids[] = $attribute->id;
            }
        }
        foreach ($mskAttrids as $mAttrid) {
            if ($mAttrid && !in_array($mAttrid, $attrids)) {
                return \ErrorCode::getError("MSK0003", $mAttrid, $fam->name, $this->name);
            }
        }
        return "";
    }

    public function getVisibilities()
    {
        $tvisid = $this->getMultipleRawValues("MSK_VISIBILITIES");
        $tattrid = $this->getMultipleRawValues("MSK_ATTRIDS");

        $tvisibilities = array();
        foreach ($tattrid as $k => $v) {
            if ($tvisid[$k] !== "-") {
                $tvisibilities[$v] = $tvisid[$k];
            }
        }
        return $tvisibilities;
    }

    public function getNeedeeds()
    {
        $tvisid = $this->getMultipleRawValues("MSK_NEEDEEDS");
        $tattrid = $this->getMultipleRawValues("MSK_ATTRIDS");

        $tvisibilities = array();
        foreach ($tattrid as $k => $v) {
            $tvisibilities[$v] = $tvisid[$k];
        }
        return $tvisibilities;
    }
}
