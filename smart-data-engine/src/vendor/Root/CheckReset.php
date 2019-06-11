<?php

class CheckReset extends CheckData
{
    protected $value;
    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    protected $doc;

    protected $authorizedKeys = array(
        "attributes",
        "default",
        "properties",
        "parameters",
        "structure",
        "enums"
    );

    /**
     * @param array                               $data
     * @param \Anakeen\Core\Internal\SmartElement $doc
     * @return CheckReset
     */
    public function check(array $data, &$doc = null)
    {
        $this->value = strtolower($data[1]);
        $this->doc = $doc;
        $this->checkValue();
        return $this;
    }

    /**
     * check reset values
     * @return void
     */
    protected function checkValue()
    {
        if ($this->value) {
            if (!in_array($this->value, $this->authorizedKeys)) {
                $this->addError(ErrorCode::getError('RESE0001', $this->value, $this->doc->name));
            }
        }
    }
}
