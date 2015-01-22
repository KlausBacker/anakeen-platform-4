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
    protected $htmlLink = "";
    const type = "common";
    const showEmptyContentOption = "showEmptyContent";
    const labelPositionOption = "labelPosition";
    const htmlLinkOption = "htmlLink";
    const buttonsOption = "buttons";
    const inputHtmlTooltip = "inputHtmlTooltip";
    const autoCompleteHtmlLabelOption = "autoCompleteHtmlLabel";
    const labelsOption = "labels";
    const autoPosition = "auto";
    const leftPosition = "left";
    const upPosition = "up";
    const nonePosition = "none";
    const formatOption = "format";
    
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
    /**
     * When value is empty, display text instead
     * The text is in HTML (it is not encoded)
     * @note use only in consultation mode
     * @param string $content formated text
     * @return $this
     */
    public function showEmptyContent($content)
    {
        return $this->setOption(self::showEmptyContentOption, $content);
    }
    /**
     * Set position label relative to its value
     * @param string $position
     * @return $this
     * @throws Exception
     */
    public function setLabelPosition($position)
    {
        $allow = array(
            self::autoPosition,
            self::leftPosition,
            self::upPosition,
            self::nonePosition
        );
        if (!in_array($position, $allow)) {
            throw new Exception("UI0201", $position, implode(', ', $allow));
        }
        return $this->setOption(self::labelPositionOption, $position);
    }
    /**
     * Add a html link on value (view mode only)
     * @note use only in view mode
     * @param HtmlLinkOptions $options
     * @return $this
     */
    public function setLink(HtmlLinkOptions $options)
    {
        $this->setOption(self::htmlLinkOption, $options);
        return $this;
    }
    /**
     * Add an html tooltip when input has focus
     * @note use only in edit mode
     * @param string  $htmlText Html fragment
     * @return $this
     */
    public function setInputTooltip($htmlText)
    {
        $this->setOption(self::inputHtmlTooltip, $htmlText);
        return $this;
    }
    /**
     * Add an html tooltip on auto complete button
     * @note use only in edit mode
     * @param string  $htmlText Html fragment
     * @return $this
     */
    public function setAutoCompleteHtmlLabel($htmlText)
    {
        $this->setOption(self::autoCompleteHtmlLabelOption, $htmlText);
        return $this;
    }
    /**
     * Add a html link on value (view mode only)
     * @note use only in edit mode
     * @param \Dcp\Ui\ButtonOptions $options
     * @return $this
     */
    public function addButton(ButtonOptions $options)
    {
        $buttons = $this->getOption(self::buttonsOption);
        if (empty($buttons)) {
            $buttons = array();
        }
        $buttons[] = $options;
        $this->setOption(self::buttonsOption, $buttons);
        return $this;
    }
    /**
     * Add or modify specific labels for widget
     * @param array $labels
     * @return $this
     */
    public function setLabels(array $labels)
    {
        $cLabels = $this->getOption(self::labelsOption);
        if (empty($cLabels)) {
            $cLabels = array();
        }
        $this->setOption(self::labelsOption, array_merge($cLabels, $labels));
        return $this;
    }
}

class HtmlLinkOptions
{
    public function __construct($url = null)
    {
        if ($url !== null) {
            $this->url = $url;
        }
    }
    /**
     * @var string title of window
     */
    public $target = "_self";
    public $windowWidth = "300px";
    public $windowHeight = "200px";
    public $windowTitle = "";
    public $title = "";
    public $url = "";
}
class ButtonOptions
{
    public function __construct($url = null)
    {
        if ($url !== null) {
            $this->url = $url;
        }
    }
    /**
     * @var string target of url
     */
    public $target = "_self";
    public $windowWidth = "300px";
    public $windowHeight = "200px";
    /**
     * @var string title of window
     * only for _dialog target
     */
    public $windowTitle = "";
    /**
     * @var string addtionnal css class
     */
    public $class = "";
    /**
     * @var string tooltip of button
     */
    public $title = "";
    /**
     * @var string button content
     * The content must be a valid Html fragment
     */
    public $htmlContent = "";
    /**
     * @var string url to launch
     */
    public $url = "";
}
