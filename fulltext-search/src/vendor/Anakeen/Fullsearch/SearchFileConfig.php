<?php

namespace Anakeen\Fullsearch;

class SearchFileConfig extends SearchFieldConfig
{
    public $filename = false;
    public $filecontent = false;
    public $filetype = false;

    public function __construct($field, $weight)
    {
        parent::__construct($field, $weight);
    }


    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            ["filecontent" => true]
        );
    }
}
