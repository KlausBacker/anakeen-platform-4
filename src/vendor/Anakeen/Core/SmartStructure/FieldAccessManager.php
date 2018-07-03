<?php

namespace Anakeen\Core\SmartStructure;

use Anakeen\Core\Internal\SmartElement;
use Dcp\Exception;

class FieldAccessManager
{
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

    }

    public static function hasReadAccess(SmartElement $se, BasicAttribute $oa)
    {
        return true;
    }
}