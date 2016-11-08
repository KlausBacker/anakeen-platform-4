<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

class HtmltextRenderOptions extends CommonRenderOptions
{
    
    const type = "htmltext";
    const toolbarOption = "toolbar";
    const heightOption = "height";
    const toolbarStartupExpandedOption = "toolbarStartupExpanded";
    const ckEditorConfigurationOption = "ckEditorConfiguration";
    const ckEditorAllowAllTagsOption = "ckEditorAllowAllTags";
    const anchorsOptions = "anchors";
    
    const fullToolbar = "Full";
    const simpleToolbar = "Simple";
    const basicToolbar = "Basic";
    const defaultToolbar = "Default";
    /**
     * Collapse or expand toolbar on startup
     * @note use only in edition mode
     * @param bool $expand : false to collapse at startup
     * @return $this
     */
    public function setToolbarStartupExpanded($expand)
    {
        return $this->setOption(self::toolbarStartupExpandedOption, (bool)$expand);
    }
    /**
     * Use a predefined or a custom toolbar
     * Predefined toolbars are "Full", "Default", "Simple", "Basic"
     * @note use only in edition mode
     * @param string|array $toolbar definition .
     *
     * @return $this
     */
    public function setToolbar($toolbar)
    {
        return $this->setOption(self::toolbarOption, $toolbar);
    }
    /**
     * Set height of text edior body
     * Need to precise unit like "px" or "em", if not "px" is used
     *
     * @note use only in edition mode
     * @param string $height the body height
     *
     * @return $this
     */
    public function setHeight($height)
    {
        return $this->setOption(self::heightOption, $height);
    }
    /**
     * Set extra configuration for ckEditor widget
     *
     * @note use only in edition mode
     * @param array $config indexed array
     *
     * @return $this
     */
    public function setCkEditorConfiguration($config)
    {
        return $this->setOption(self::ckEditorConfigurationOption, $config);
    }
    /**
     * Allow all HTML tags in htlm source
     *
     * @note use only in edition mode
     * @param bool $allow set to true to allow al tags else depends of ckEditor toolbar configuration
     * @see setToolbar
     * @return $this
     */
    public function setCkEditorAllowAllTags($allow)
    {
        return $this->setOption(self::ckEditorAllowAllTagsOption, (bool)$allow);
    }

    /**
     * Add a html link on value (view mode only)
     * @note use only in view mode
     * @param anchorOptions $options
     * @return $this
     */
    public function setAnchorsOptions(anchorOptions $options)
    {
        $this->setOption(self::anchorsOptions, $options);
        return $this;
    }
}

class anchorOptions
{
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
     * @var bool modal window
     */
    public $modal = false;
}
