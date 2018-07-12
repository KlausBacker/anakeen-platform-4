<?php

namespace Anakeen\Core\SmartStructure;

use Anakeen\Core\Account;
use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use SmartStructure\Fields\Fieldaccesslayerlist as FallFields;
use SmartStructure\Fields\Fieldaccesslayer as FalFields;
use Dcp\Exception;

class FieldAccessManager
{
    protected static $fa = [];
    public static $mb = 0;
    public static $mbc;

    public static function getRawAccess(string $accessibility)
    {
        switch ($accessibility) {
            case "Read":
                return BasicAttribute::READ_ACCESS;
            case "Write":
                return BasicAttribute::WRITE_ACCESS;
            case "ReadWrite":
                return BasicAttribute::READWRITE_ACCESS;
            case "None":
                return BasicAttribute::NONE_ACCESS;
        }
        throw new Exception("ATTR0803", $accessibility);
    }

    public static function getTextAccess(int $accessibility)
    {
        switch ($accessibility) {
            case BasicAttribute::READ_ACCESS:
                return "Read";
            case BasicAttribute::WRITE_ACCESS:
                return "Write";
            case BasicAttribute::READWRITE_ACCESS:
                return "ReadWrite";
            case BasicAttribute::NONE_ACCESS:
                return "None";
        }
        return "";
    }

    public static function getAccess(SmartElement $se, BasicAttribute $oa)
    {
        if ($se->fallid === null) {
            return $oa->getAccess();
        }
        /** @var \SmartStructure\Fieldaccesslayerlist $fall */
        $fall = SEManager::getDocument($se->fallid, false);
        SEManager::cache()->addDocument($fall);
        return self::getFalAccess($fall, $se, $oa);
    }

    protected static function getFalAccess(\SmartStructure\Fieldaccesslayerlist $fall, SmartElement $se, BasicAttribute $oa)
    {
        $dynProfil = $fall->getRawValue(FallFields::dpdoc_famid);

        if ($dynProfil) {
            $fall->set($se);
            $seIndex = $se->id;
        } else {
            $seIndex = 0;
        }
        if (!isset(self::$fa[$fall->id][$seIndex])) {
            self::$fa[$fall->id][$seIndex] = [];
            $layers = $fall->getMultipleRawValues(FallFields::fall_layer);
            $aclNames = $fall->getMultipleRawValues(FallFields::fall_aclname);
            foreach ($layers as $k => $layerId) {
                if ($fall->hasPermission($aclNames[$k])) {
                    $layerData = SEManager::getRawDocument($layerId, false);
                    $fields = SmartElement::rawValueToArray($layerData[falFields::fal_fieldid]);
                    $access = SmartElement::rawValueToArray($layerData[falFields::fal_fieldaccess]);

                    foreach ($fields as $kf => $field) {
                        if (!isset(self::$fa[$fall->id][$seIndex][$field])) {
                            self::$fa[$fall->id][$seIndex][$field] = 0;
                        }
                        self::$fa[$fall->id][$seIndex][$field] |= self::getRawAccess($access[$kf]);
                    }
                }
            }
        }
        return self::getMAccess($oa, self::$fa[$fall->id][$seIndex]);
    }

    protected static function getMAccess(BasicAttribute $oa, &$cache)
    {
        $mAccess = isset($cache[$oa->id]) ? ($oa->access | $cache[$oa->id]) : $oa->access;
        return (!$oa->fieldSet) ? $mAccess : self::getMAccess($oa->fieldSet, $cache) & $mAccess;
    }

    public static function hasReadAccess(SmartElement $se, BasicAttribute $oa)
    {
       // print_r([ContextManager::getCurrentUser()->id, $oa->id, $oa->access, $oa->getAccess()]);
        $mb0 = microtime(true);
        $x = (ContextManager::getCurrentUser()->id == Account::ADMIN_ID) ||
            ($oa->getAccess() & BasicAttribute::READ_ACCESS) ||
            ($se->fallid !== null && (self::getAccess($se, $oa) & BasicAttribute::READ_ACCESS));

        self::$mb += (microtime(true) - $mb0);
        self::$mbc++;
        return $x;
    }
}
