<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class DocidRenderOptions extends CommonRenderOptions
{
    
    use TFormatRenderOption;
    const type = "docid";
    const kendoMultiSelectConfigurationOption = "kendoMultiSelectConfiguration";
    const listDisplay = "list";
    const autocompletionDisplay = "autoCompletion";
    const multipleSingleDisplay = "singleMultiple";
    const displayOption = "editDisplay";
    const documentIconSizeOption = "documentIconSize";
    /**
     * Set extra configuration for kendoMultiSelect widget
     *
     * @note use only in edition mode
     * @param array $config indexed array
     *
     * @return $this
     */
    public function setKendoMultiSelectConfiguration($config)
    {
        $opt = $this->getOption(self::kendoMultiSelectConfigurationOption);
        if (is_array($opt)) {
            $config = array_merge($opt, $config);
        }
        return $this->setOption(self::kendoMultiSelectConfigurationOption, $config);
    }
    /**
     * Text to set into input when is empty
     * @note use only in edition mode
     * @param string $text text to display
     * @return $this
     */
    public function setPlaceHolder($text)
    {
        return $this->setOption(self::placeHolderOption, $text);
    }
    /**
     * Display format
     * @note use only in edition mode
     * @param string $display one of vertical, horizontal, select, autoCompletion, bool
     * @return $this
     * @throws Exception
     */
    public function setDisplay($display)
    {
        $allow = array(
            self::listDisplay,
            self::multipleSingleDisplay,
            self::autocompletionDisplay
        );
        if (!in_array($display, $allow)) {
            throw new Exception("UI0210", $display, implode(', ', $allow));
        }
        return $this->setOption(self::displayOption, $display);
    }
    
    public function addCreateDocumentButton(CreateDocumentOptions $options)
    {
        $options->target = "_dialog";
        $options->url = "";
        $options->class.= " dcpAttribute__content__button--create";
        $options->class = trim($options->class);
        $this->addButton($options);
    }
    /**
     * Define icon geometry of relation
     * 200 : width 200px
     * x300 : height 300px
     * 200x300 : width 200 height 300 (define the box where image dimension can be included)
     * 200x300c : width 200 height 300 with crop to get exact dimension
     * 200x300s : width 200 height 300 with streched image to get exact dimension
     *
     * @note use only in view mode
     *
     * @param string $size in px (number to define width, xNumber to define Height, or WidthxHeight) : 300, x450, 200x300
     *
     * @return $this
     * @throws Exception
     */
    public function setDocumentIconSize($size)
    {
        if (!preg_match("/^x?[0-9]+$/", $size) && !preg_match("/^[0-9]+x[0-9]+[fsc]?$/", $size)) {
            throw new Exception("UI0212", $size);
        }
        return $this->setOption(self::documentIconSizeOption, $size);
    }
}

class CreateDocumentOptions extends \Dcp\Ui\ButtonOptions
{
    public $familyName;
    public $createLabel;
    public $htmlCreateContent;
    public $htmlEditContent;
    public $formValues = [];
    
    public function __construct($familyName = null)
    {
        parent::__construct();
        $this->familyName = $familyName;
        $this->htmlCreateContent = '<i class="fa fa-plus-circle" />';
        $this->htmlEditContent = '<i class="fa fa-pencil" />';
        $this->createLabel = ___("Create and insert to \"{{label}}\"", "ddui");
        $this->updateLabel = ___("Save and update \"{{label}}\"", "ddui");
        $this->windowWidth = "479px";
        $this->windowHeight = "400px";
    }
}
