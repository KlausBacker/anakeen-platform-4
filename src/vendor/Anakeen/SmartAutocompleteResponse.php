<?php

namespace Anakeen;

class SmartAutocompleteResponse
{
    protected $data = [];
    protected $error="";
    protected $outputs = [];

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError(string $error): void
    {
        $this->error = $error;
    }
    /**
     * @return array
     */
    public function getOutputs(): array
    {
        return $this->outputs;
    }

    /**
     * @param array $outputs
     */
    public function setOutputs(array $outputs): void
    {
        $this->outputs = $outputs;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Add a entry in autocomplete list
     * @param string $htmlLabel html label (must be encoded if it is a raw value)
     * @param array        $data
     */
    public function appendEntry($htmlLabel, array $data)
    {
        $outputData = [];
        foreach ($data as $k => $v) {
            if (is_string($v)) {
                $v = ["value" => $v, "displayValue" => $v];
            }
            $outputData[$this->outputs[$k]] = $v;
        }
        $this->data[] = [
            "title" => $htmlLabel,
            "values" => $outputData
        ];
    }
}
