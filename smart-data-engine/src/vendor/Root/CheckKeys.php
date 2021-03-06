<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckKeys extends CheckData
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
     * @return CheckKeys
     */
    public function check(array $data, &$extra = null)
    {
        $this->famName = (isset($data[1])) ? trim($data[1]) : null;
        $this->attrIds = \Anakeen\Exchange\Utils::getOrder($data);

        $this->checkKeysFamily();
        if (!$this->hasErrors()) {
            $this->checkKeyscount();
        }
        if (!$this->hasErrors()) {
            $this->checkKeysAttribute();
        }

        return $this;
    }

    /**
     * check
     * check
     *
     * @return void
     */
    protected function checkKeysFamily()
    {
        if ($this->famName) {
            if (!$this->checkName($this->famName)) {
                $this->addError(ErrorCode::getError('KEYS0001', $this->famName));
            } else {
                try {
                    $this->family = \Anakeen\Core\SEManager::getDocument($this->famName);
                    if (!$this->family || !$this->family->isAlive()) {
                        $this->addError(ErrorCode::getError('KEYS0002', $this->famName));
                    } else {
                        if ($this->family->doctype != 'C') {
                            $this->addError(ErrorCode::getError('KEYS0003', $this->famName));
                        } else {
                            $canCreateError = $this->family->control('create');
                            if ($canCreateError) {
                                $this->addError(ErrorCode::getError('KEYS0004', $this->famName));
                            }
                        }
                    }
                } catch (Exception $e) {
                    $this->addError(ErrorCode::getError('KEYS0005', $this->famName, $e->getMessage()));
                }
            }
        } else {
            $this->addError(ErrorCode::getError('KEYS0006'));
        }
    }

    /**
     * check logical name
     *
     * @return void
     */
    protected function checkKeysAttribute()
    {
        if ($this->family) {
            foreach ($this->attrIds as $aid) {
                if (!$this->family->getAttribute($aid)) {
                    $this->addError(ErrorCode::getError('KEYS0100', $aid, $this->family->name));
                }
            }
        }
    }

    /**
     * check one or two keys
     *
     * @return void
     */
    protected function checkKeyscount()
    {
        $c = 0;
        foreach ($this->attrIds as $aid) {
            if ($aid) {
                $c++;
            }
        }
        if ($c === 0) {
            $this->addError(ErrorCode::getError('KEYS0101', $this->family->name));
        } elseif ($c > 2) {
            $this->addError(ErrorCode::getError('KEYS0102', implode(', ', $this->attrIds), $this->family->name));
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
