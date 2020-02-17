<?php

namespace Anakeen\Fullsearch;

class SearchFileConfig extends SearchFieldConfig
{
    public $filename = false;
    public $filecontent = false;
    public $filetype = false;

    public function __construct($field, $weight, $filename, $filecontent, $filetype)
    {
        parent::__construct($field, $weight);
        $this->filename = ($filename === "true" || $filename === true);
        $this->filecontent = ($filecontent === "true" || $filecontent === true);
        $this->filetype = ($filetype === "true" || $filetype === true);
    }


    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            ["filename" => $this->filename, "filecontent" => $this->filecontent, "filetype" => $this->filetype]
        );
    }
}
