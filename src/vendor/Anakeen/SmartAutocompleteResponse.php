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
     * @return SmartAutocompleteResponse
     */
    public function setError(string $error): SmartAutocompleteResponse
    {
        $this->error = $error;
        return $this;
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
     * @return SmartAutocompleteResponse
     */
    public function setOutputs(array $outputs): SmartAutocompleteResponse
    {
        $this->outputs = $outputs;
        return $this;
    }

    /**
     * @param array $data
     * @return SmartAutocompleteResponse
     */
    public function setData(array $data): SmartAutocompleteResponse
    {
        $this->data = $data;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Add a entry in autocomplete list
     * @param string $htmlLabel html label (must be encoded if it is a raw value)
     * @param array  $data
     * @return SmartAutocompleteResponse
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
        return $this;
    }
}
