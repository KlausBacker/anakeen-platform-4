<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

use Dcp\HttpApi\V1\DocManager\DocManager;

class CommonRenderOptions extends BaseRenderOptions
{
    const type = "common";
    const showEmptyContentOption = "showEmptyContent";
    const labelPositionOption = "labelPosition";
    const htmlLinkOption = "htmlLink";
    const buttonsOption = "buttons";
    const templateOption = "template";
    const templateKeysOption = "templateKeys";
    const inputHtmlTooltip = "inputHtmlTooltip";
    const displayDeleteButtonOption = "displayDeleteButton";
    const autoCompleteHtmlLabelOption = "autoCompleteHtmlLabel";
    const descriptionOption = "description";
    const translatedLabelsOption = "translatedLabels";
    const placeHolderOption = "placeHolder";
    const attributeLabelOption = "attributeLabel";
    const formatOption = "format";
    const helpLinkIdentifierOption = "helpLinkIdentifier";
    
    const autoPosition = "auto";
    const leftPosition = "left";
    const upPosition = "up";
    const nonePosition = "none";
    
    const topPosition = "top";
    const bottomPosition = "bottom";
    //const leftPosition="left";
    const rightPosition = "right";
    const topLabelPosition = "topLabel";
    const topValuePosition = "topValue";
    const bottomLabelPosition = "bottomLabel";
    const bottomValuePosition = "bottomValue";
    const clickPosition = "click";
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
     * At custom template for an attribute
     * @param string $htmlText mustache template
     * @param array $extraKeys extra data for template
     * @return $this
     */
    public function setTemplate($htmlText, array $extraKeys = array())
    {
        $this->setOption(self::templateOption, $htmlText);
        $this->setOption(self::templateKeysOption, $extraKeys);
        
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
     * Display or not the delete button
     * @note use only in edit mode
     * @param bool $display set true to show (by default), false to hide delete button
     * @return $this
     */
    public function displayDeleteButton($display)
    {
        $this->setOption(self::displayDeleteButtonOption, (bool)$display);
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
     * Add an html text near the attribute
     *
     * @param string $htmlTitle   Html text short description
     * @param string $position    position : top, bottom, left, topLabel, topValue, bottomLabel, bottomValue, right, click
     *
     * @param string $htmlContent Html text long description
     * @param bool   $collapsed   if true the long description is collapsed (need click to see it)
     *
     * @return $this
     * @throws Exception
     */
    public function setDescription($htmlTitle, $position = "top", $htmlContent = "", $collapsed = false)
    {
        $allow = array(
            self::topPosition,
            self::bottomPosition,
            self::leftPosition,
            self::rightPosition,
            self::topLabelPosition,
            self::topValuePosition,
            self::bottomLabelPosition,
            self::bottomValuePosition,
            self::clickPosition
        );
        if (!in_array($position, $allow)) {
            throw new Exception("UI0205", $position, implode(', ', $allow));
        }
        if (!is_string($htmlContent)) {
            throw new Exception("UI0206", gettype($htmlContent));
        }
        if (!is_bool($collapsed)) {
            throw new Exception("UI0207", gettype($collapsed));
        }
        $this->setOption(self::descriptionOption, array(
            "htmlTitle" => $htmlTitle,
            "htmlContent" => $htmlContent,
            "position" => $position,
            "collapsed" => $collapsed
        ));
        return $this;
    }
    /**
     * Add a html link on value
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
     * Modify default label attribute
     *
     * @param string $label
     * @return $this
     */
    public function setAttributeLabel($label)
    {
        $this->setOption(self::attributeLabelOption, $label);
        return $this;
    }
    /**
     * Add or modify specific messages for widget
     * @param array $labels
     * @return $this
     */
    public function setTranslations(array $labels)
    {
        $cLabels = $this->getOption(self::translatedLabelsOption);
        if (empty($cLabels)) {
            $cLabels = array();
        }
        $this->setOption(self::translatedLabelsOption, array_merge($cLabels, $labels));
        return $this;
    }

    /**
     * Display help document links before attribute label
     *
     * @param string $documentIdentifier must identifier a HELP family document
     *
     * @return $this
     * @throws Exception
     */
    public function setLinkHelp($documentIdentifier)
    {
        $helpDocument=DocManager::getDocument($documentIdentifier);
        if (!$helpDocument || !is_a($helpDocument, "\\Dcp\\Family\\HelpPage")) {
            throw new Exception("UI0208", $helpDocument->fromname);
        }
        DocManager::cache()->addDocument($helpDocument);
        return $this->setOption(self::helpLinkIdentifierOption, $documentIdentifier);
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
     * @var string target of window
     */
    public $target = "_self";
    /**
     * @var string width of window
     */
    public $windowWidth = "300px";
    /**
     * @var string height of window
     */
    public $windowHeight = "200px";
    /**
     * @var string title of window
     */
    public $windowTitle = "";
    /**
     * @var string tooltip text
     */
    public $title = "";
    /**
     * @var string url link for single value
     */
    public $url = "";
    /**
     * @var string[] url links for multiple value
     */
    public $urls = array();
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
