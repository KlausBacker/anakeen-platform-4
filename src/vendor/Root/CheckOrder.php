<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckOrder extends CheckData
{
    /**
     * @var string family reference
     */
    protected $famName;
    /**
     * @var array
     */
    protected $attrIds;
    /**
     * @var \Anakeen\Core\SmartStructure
     */
    protected $family;

    /**
     * @param array $data
     * @param null  $extra
     *
     * @return $this
     */
    public function check(array $data, &$extra = null)
    {
        $this->famName = isset($data[1]) ? trim($data[1]) : null;
        $this->attrIds = getOrder($data);

        $this->CheckOrderFamily();
        if (!$this->hasErrors()) {
            $this->CheckOrderAttribute();
        }

        return $this;
    }

    /**
     * Get the parsed family name or bool(false) if family name could
     * not be parsed.
     *
     * @return bool|string
     */
    public function getParsedFamName()
    {
        return (isset($this->famName) ? $this->famName : false);
    }

    /**
     * Get the parsed attributes ids or bool(false) if attributes ids
     * could not be parsed.
     *
     * @return array|bool
     */
    public function getParsedAttrIds()
    {
        return (isset($this->attrIds) ? $this->attrIds : false);
    }

    /**
     * check
     * check
     * @return void
     */
    protected function checkOrderFamily()
    {
        if ($this->famName) {
            if (!$this->checkName($this->famName)) {
                $this->addError(ErrorCode::getError('ORDR0001', $this->famName));
            } else {
                try {
                    $this->family = Anakeen\Core\SEManager::getDocument($this->famName);
                    if (!$this->family) {
                        $this->addError(ErrorCode::getError('ORDR0002', $this->famName));
                    } else {
                        if ($this->family->doctype !== 'C') {
                            $this->addError(ErrorCode::getError('ORDR0003', $this->famName));
                        } else {
                            $canCreateError = $this->family->control('create');
                            if ($canCreateError) {
                                $this->addError(ErrorCode::getError('ORDR0004', \Anakeen\Core\ContextManager::getCurrentUser()->login, $this->famName));
                            }
                        }
                    }
                } catch (Exception $e) {
                    $this->addError(ErrorCode::getError('ORDR0005', $this->famName, $e->getMessage()));
                }
            }
        } else {
            $this->addError(ErrorCode::getError('ORDR0006'));
        }
    }

    /**
     * check logical name
     * @return void
     */
    protected function checkOrderAttribute()
    {
        if ($this->family) {
            foreach ($this->attrIds as $aid) {
                if ($aid && (!$this->family->getAttribute($aid)) && (strpos($aid, "extra:") !== 0)) {
                    var_dump(array_keys($this->family->attributes->attr));
                    $this->addError(ErrorCode::getError('ORDR0100', $aid, $this->family->name));
                }
            }
        }
    }

    private function checkName($name)
    {
        if ($name && (!is_numeric($name))) {
            if (!preg_match('/^[a-z][a-z0-9_]*$/i', $name)) {
                return false;
            }
        }
        return true;
    }
}
