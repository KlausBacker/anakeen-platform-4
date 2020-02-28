<?php


namespace Anakeen\Fullsearch;

class SearchConfig implements \JsonSerializable
{
    public $structure;
    public $collection;
    /** @var SearchFieldConfig[] */
    public $fields = [];
    /**
     * @var SearchFileConfig[]
     */
    public $files = [];
    /**
     * @var SearchCallableConfig[]
     */
    public $callables = [];

    public function __construct($dataConfig = [])
    {
        if ($dataConfig) {
            $this->structure = $dataConfig["structure"];
            $this->collection = $dataConfig["collection"]??"";
            foreach ($dataConfig["fields"] as $fieldConfig) {
                $this->fields[] = new SearchFieldConfig($fieldConfig["field"], $fieldConfig["weight"]);
            }

            foreach ($dataConfig["files"] as $fieldConfig) {
                $this->files[] = new SearchFileConfig(
                    $fieldConfig["field"],
                    $fieldConfig["weight"]
                );
            }
            foreach ($dataConfig["callables"] as $fieldConfig) {
                $this->callables[] = new SearchCallableConfig(
                    $fieldConfig["function"],
                    $fieldConfig["weight"]
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            "structure" => $this->structure,
            "collection" => $this->collection,
            "fields" => $this->fields,
            "files" => $this->files,
            "callables" => $this->callables
        ];
    }
}
