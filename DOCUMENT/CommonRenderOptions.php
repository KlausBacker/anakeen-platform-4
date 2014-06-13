<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class CommonRenderOptions
{
    /**
     * @var RenderOptions
     */
    protected $optionObject = null;

    protected $localOptionName = null;


    protected $localOptionValue = null;
    protected $scope = null;
    const type = "common";
    const showEmptyContentOption = "showEmptyContent";
    const labelPositionOption = "labelPosition";
    
    const leftPosition = "left";
    const upPosition = "up";
    const nonePosition = "none";
    
    public function __construct(RenderOptions &$options=null)
    {
        $this->optionObject = $options;
    }
    /**
     * @return null
     */
    public function getLocalOptionName()
    {
        return $this->localOptionName;
    }

    /**
     * @return null
     */
    public function getLocalOptionValue()
    {
        return $this->localOptionValue;
    }
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }
    public function getScope()
    {
        return $this->scope;
    }
    public function setOption($optName, $optValue)
    {
        if ($this->optionObject) {
        if ($this->scope) {
            $this->optionObject->setAttributeScopeOption($this->scope, $optName, $optValue);
        } else {
            $this->optionObject->setAttributeTypeOption(static::type, $optName, $optValue);
        }
        } else {
            $this->localOptionName=$optName;
            $this->localOptionValue=$optValue;
        }
        return $this;
    }
    
    public function showEmptyContent($content)
    {
        
        return $this->setOption(self::showEmptyContentOption, $content);
    }
    public function labelPosition($position)
    {
        $allow = array(
            self::leftPosition,
            self::upPosition,
            self::nonePosition
        );
        if (!in_array($position, $allow)) {
            throw new Exception("UI0201", $position, implode(', ', $allow));
        }
        return $this->setOption(self::labelPositionOption, $position);
    }
}
