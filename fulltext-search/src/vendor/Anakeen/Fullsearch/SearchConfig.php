<?php


namespace Anakeen\Fullsearch;

class SearchConfig implements \JsonSerializable
{
    public $structure;
    /** @var SearchFieldConfig[] */
    public $fields = [];
    /**
     * @var SearchFileConfig[]
     */
    public $files = [];

    public function __construct($dataConfig = [])
    {
        if ($dataConfig) {
            $this->structure = $dataConfig["structure"];
            foreach ($dataConfig["fields"] as $fieldConfig) {
                $this->fields[] = new SearchFieldConfig($fieldConfig["field"], $fieldConfig["weight"]);
            }

            foreach ($dataConfig["files"] as $fieldConfig) {
                $this->files[] = new SearchFileConfig(
                    $fieldConfig["field"],
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
            "fields" => $this->fields,
            "files" => $this->files
        ];
    }
}
