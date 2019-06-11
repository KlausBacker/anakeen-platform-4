<?php

namespace Anakeen;

class SmartAutocompleteResponse
{
    protected $data = [];
    protected $error = "";
    protected $outputs = [];

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error?:$this->isOutputComplete();
    }

    /**
     * @param string $error
     *
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
     *
     * @return SmartAutocompleteResponse
     */
    public function setOutputs(array $outputs): SmartAutocompleteResponse
    {
        $this->outputs=$outputs;
        return $this;
    }

    /**
     * @param array $data
     *
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
     *
     * @param string $htmlLabel html label (must be encoded if it is a raw value)
     * @param array  $data
     *
     * @return SmartAutocompleteResponse
     */
    public function appendEntry($htmlLabel, array $data)
    {
        $outputData = [];
        $skipData = [];
        foreach ($data as $k => $v) {
            if (is_string($v) || is_numeric($v)) {
                $v = ["value" => $v, "displayValue" => $v];
            }
            if (isset($this->outputs[$k])) {
                $outputData[$this->outputs[$k]] = $v;
            } else {
                $skipData[$k] = $v;
            }
        }

        $this->data[] = [
            "title" => $htmlLabel,
            "values" => $outputData,
            "skips" => $skipData
        ];
        return $this;
    }

    protected function isOutputComplete()
    {
        foreach ($this->outputs as $kout => $outField) {
            foreach ($this->data as $datum) {
                if (!isset($datum["values"][$outField])) {
                    $err = \ErrorCode::getError("ATTR1102", $kout, $outField);
                    return $err;
                }
            }
        }
        return "";
    }
}
