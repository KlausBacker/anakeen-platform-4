<?php

namespace Anakeen\Pu\Config;

use Anakeen\EnumItem;

class EnumTesting
{
    public static function letters()
    {
        $items = [];

        $a = ord('A');
        $z = ord('Z');
        for ($i = $a; $i <= $z; $i++) {
            $items[] = new EnumItem(chr($i), "Letter " . strtolower(chr($i)));
        }
        return $items;
    }
}