<?php

namespace Anakeen\Core\SmartStructure;

class EnumLocale
{

    public $lang;
    public $label;

    public function __construct($lang, $label)
    {
        $this->lang = $lang;
        $this->label = $label;
    }
}
