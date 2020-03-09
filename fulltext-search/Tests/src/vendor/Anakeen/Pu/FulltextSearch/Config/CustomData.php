<?php


namespace Anakeen\Pu\FulltextSearch\Config;

use Anakeen\Core\Internal\SmartElement;

class CustomData
{
    public static function addHisto(SmartElement $smartElement)
    {
        $history=$smartElement->getHisto();
        $s="";
        foreach ($history as $item) {
            $s.=sprintf("%s - %s ", $item["code"], $item["comment"]);
        }
        return $s;
    }

    public static function getName(SmartElement $smartElement)
    {
        return $smartElement->name;
    }
}
