<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class HtmltextRenderOptions extends CommonRenderOptions
{
    const type = "htmltext";
    const toolbarButtonsOption = "toolbarButtons";
    const heightOption = "height";
    const toolbarStartupExpandedOption = "toolbarStartupExpanded";
    const ckEditorConfigurationOption = "ckEditorConfiguration";
    const ckEditorAllowAllTagsOption = "ckEditorAllowAllTags";
    const anchorsOptions = "anchors";

    const fullToolbar = "Full";
    const simpleToolbar = "Simple";
    const basicToolbar = "Basic";


    const basicButtons=[
        "bold",
        "italic",
        // ----------
        "insertUnorderedList",
        "insertOrderedList",
        // ----------
        "createLink",
        "unlink"
    ];
    const simpleButtons=[
        "bold",
        "italic",
        "underline",
        "strikethrough",
        "cleanFormatting",

        // ----------
        "insertUnorderedList",
        "insertOrderedList",

        "indent",
        "outdent",

        "justifyLeft",
        "justifyCenter",
        "justifyRight",
        "justifyFull",

        "createLink",
        "unlink",

        "insertImage",

        "tableWizard",
        "createTable",
        "addRowAbove",
        "addRowBelow",
        "addColumnLeft",
        "addColumnRight",
        "deleteRow",
        "deleteColumn",
        "mergeCellsHorizontally",
        "mergeCellsVertically",
        "splitCellHorizontally",
        "splitCellVertically",

        "viewHtml",
        "formatting",
        "cleanFormatting",
        "copyFormat",
        "applyFormat",
        "fontName",
        "fontSize",
        "foreColor",
        "backColor",
        "print"
    ];
    const fullButtons=[
        "bold",
        "italic",
        "underline",
        "strikethrough",
        "justifyLeft",
        "justifyCenter",
        "justifyRight",
        "justifyFull",
        "insertUnorderedList",
        "insertOrderedList",
        "indent",
        "outdent",
        "createLink",
        "unlink",
        "insertImage",
        "insertFile",
        "subscript",
        "superscript",
        "tableWizard",
        "createTable",
        "addRowAbove",
        "addRowBelow",
        "addColumnLeft",
        "addColumnRight",
        "deleteRow",
        "deleteColumn",
        "mergeCellsHorizontally",
        "mergeCellsVertically",
        "splitCellHorizontally",
        "splitCellVertically",
        "viewHtml",
        "formatting",
        "cleanFormatting",
        "copyFormat",
        "applyFormat",
        "fontName",
        "fontSize",
        "foreColor",
        "backColor",
        "print"
    ];

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
     * Predefined toolbars are "Full", "Simple", "Basic"
     * @note use only in edition mode
     * @param string|array $toolbar definition .
     *
     * @return $this
     */
    public function setToolbar($toolbar)
    {
        switch ($toolbar) {
            case self::fullToolbar:
                $buttons=self::fullButtons;
                break;
            case self::simpleToolbar:
                $buttons=self::simpleButtons;
                break;
            case self::basicToolbar:
                $buttons=self::basicButtons;
                break;
            default:
                throw new Exception("UI0215", $toolbar, implode(", ", [self::fullToolbar,self::basicToolbar, self::simpleToolbar ]));
        }
        return $this->setOption(self::toolbarButtonsOption, $buttons);
    }

    /**
     * Set height of text editor body
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
     * @return $this
     * @see setToolbar
     */
    public function setCkEditorAllowAllTags($allow)
    {
        return $this->setOption(self::ckEditorAllowAllTagsOption, (bool)$allow);
    }


    /**
     * Add a html link on value (view mode only)
     * @note use only in view mode
     * @param AnchorOptions $options
     * @return $this
     */
    public function setAnchorsOptions(AnchorOptions $options)
    {
        $this->setOption(self::anchorsOptions, $options);
        return $this;
    }
}
