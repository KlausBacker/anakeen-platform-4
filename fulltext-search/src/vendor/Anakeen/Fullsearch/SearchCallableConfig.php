<?php

namespace Anakeen\Fullsearch;

class SearchCallableConfig implements \JsonSerializable
{

    public $functionReference;
    public $weight;

    public function __construct($functionRef, $weight)
    {
        $this->functionReference=$functionRef;
        $this->weight=$weight;
    }


    public function jsonSerialize()
    {
        return ["function"=>$this->functionReference, "weight" => $this->weight];
    }
}
