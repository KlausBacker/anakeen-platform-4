<?php

namespace Anakeen;

class EnumItem
{
    public $key;
    public $label;
    /** @var EnumItem[]  */
    public $childs;

    public function __construct($key, $label = null, $childs = [])
    {
        $this->key = $key;
        $this->label = $label?:$key;
        $this->childs = $childs;
    }
}
