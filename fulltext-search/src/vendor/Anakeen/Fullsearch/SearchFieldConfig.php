<?php


namespace Anakeen\Fullsearch;


class SearchFieldConfig implements \JsonSerializable
{

    public $field;
    public $weight;

    public function __construct($field,$weight )
    {
        $this->field=$field;
        $this->weight=$weight;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return ["field"=>$this->field, "weight" => $this->weight];
    }
}