<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckInitial extends CheckData
{
    protected $InitialName;
    protected $InitialValue = '';
    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    protected $doc;
    /**
     * @var \Anakeen\Exchange\ImportDocumentDescription
     */
    protected $importer = null;

    public function __construct(\Anakeen\Exchange\ImportDocumentDescription &$importer = null)
    {
        $this->importer = $importer;
    }

    /**
     * @param array $data
     * @param \Anakeen\Core\Internal\SmartElement $doc
     * @return CheckInitial
     */
    public function check(array $data, &$doc = null)
    {
        $this->InitialName = trim(strtolower($data[1]));
        if (isset($data[2])) {
            $this->InitialValue = trim($data[2]);
        }
        $this->doc = $doc;
        $this->checkInitialName();
        $this->checkInitialValue();
        return $this;
    }

    /**
     * check Initial name syntax
     * @return void
     */
    protected function checkInitialName()
    {
        if ($this->InitialName) {
            if (!CheckAttr::checkAttrSyntax($this->InitialName)) {
                $this->addError(ErrorCode::getError('INIT0001', $this->InitialName, $this->doc->name));
            }
        } else {
            $this->addError(ErrorCode::getError('INIT0002', $this->doc->name));
        }
    }

    /**
     * check Initial value if seems to be method
     * @return void
     */
    protected function checkInitialValue()
    {
        if (\Anakeen\Core\Internal\SmartElement::seemsMethod($this->InitialValue)) {
            $oParse = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
            $strucFunc = $oParse->parse($this->InitialValue, true);
            if ($err = $strucFunc->getError()) {
                $this->addError(ErrorCode::getError(
                    'INIT0003',
                    $this->InitialName,
                    $this->InitialValue,
                    $this->doc->name,
                    $err
                ));
            }
        } elseif ($this->InitialValue) {
            $dbattr = $this->importer->getSmartField($this->InitialName);
            if ($dbattr) {
                if ($dbattr->isMultiple() && !is_array($this->InitialValue)) {
                    $value = json_decode($this->InitialValue);
                    if (!is_array($value)) {
                        try {
                            $value = \Anakeen\Core\Utils\Postgres::stringToArray($this->InitialValue);
                        } catch (\Anakeen\Exception $e) {
                        }
                    }
                    if (!is_array($value)) {
                        $this->addError(ErrorCode::getError(
                            'INIT0007',
                            $this->doc->name,
                            $this->InitialName,
                            $this->InitialValue
                        ));
                    }
                }
            }
        }
    }
}
