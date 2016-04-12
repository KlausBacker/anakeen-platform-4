<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class BaseRenderOptions
{
    /**
     * @var RenderOptions
     */
    protected $optionObject = null;
    
    protected $localOptionName = null;
    
    protected $localOptionValue = null;
    protected $scope = null;
    const type = "base";
    
    public function __construct(RenderOptions & $options = null)
    {
        $this->optionObject = $options;
    }
    /**
     * @protected internal usage
     * @return null
     */
    public function getLocalOptionName()
    {
        return $this->localOptionName;
    }
    /**
     * @protected internal usage
     * @return null
     */
    public function getLocalOptionValue()
    {
        return $this->localOptionValue;
    }
    /**
     * Use option for a specific attribute
     * @param string $scope attribute identifier
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }
    public function getScope()
    {
        return $this->scope;
    }
    /**
     * add custom option to be propagated to client
     * @param string $optName option name
     * @param string $optValue option value
     * @return $this
     */
    public function setOption($optName, $optValue)
    {
        if ($this->optionObject) {
            if ($this->scope) {
                $this->optionObject->setAttributeScopeOption($this->scope, $optName, $optValue);
            } else {
                $this->optionObject->setAttributeTypeOption(static::type, $optName, $optValue);
            }
        } else {
            $this->localOptionName = $optName;
            $this->localOptionValue = $optValue;
        }
        return $this;
    }
    
    public function getOption($optName)
    {
        if ($this->optionObject) {
            if ($this->scope) {
                return $this->optionObject->getAttributeScopeOption($this->scope, $optName);
            } else {
                return $this->optionObject->getAttributeTypeOption(static::type, $optName);
            }
        }
        return null;
    }
}
