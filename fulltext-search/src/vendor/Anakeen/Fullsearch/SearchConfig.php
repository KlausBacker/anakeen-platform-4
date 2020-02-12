<?php


namespace Anakeen\Fullsearch;

class SearchConfig implements \JsonSerializable
{
    public $structure;
    /** @var SearchFieldConfig[] */
    public $fields = [];

    public function __construct($dataConfig = [])
    {
        if ($dataConfig) {
            $this->structure = $dataConfig["structure"];
            foreach ($dataConfig["fields"] as $fieldConfig) {
                $this->fields[] = new SearchFieldConfig($fieldConfig["field"], $fieldConfig["weight"]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $data = [
            "structure" => $this->structure,
            "fields" => $this->fields
        ];
        return $data;
    }
}
