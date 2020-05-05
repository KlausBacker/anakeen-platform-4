<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

class HtmltextRenderOptions extends CommonRenderOptions
{
    const type = "htmltext";
    const toolbarOption = "toolbar";
    const heightOption = "height";
    const toolbarStartupExpandedOption = "toolbarStartupExpanded";
    const kendoEditorConfigurationOption = "kendoEditorConfiguration";
    const anchorsOptions = "anchors";

    const fullToolbar = "Full";
    const simpleToolbar = "Simple";
    const basicToolbar = "Basic";


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
        $allowedToolbar = [self::fullToolbar, self::basicToolbar, self::simpleToolbar];
        if (!in_array($toolbar, $allowedToolbar)) {
            throw new Exception(
                "UI0215",
                $toolbar,
                implode(", ", $allowedToolbar)
            );
        }

        return $this->setOption(self::toolbarOption, $toolbar);
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
     * Set extra configuration for kendoEditor widget
     *
     * @note use only in edition mode
     * @param array $config indexed array
     *
     * @return $this
     */
    public function setKendoEditorConfiguration($config)
    {
        return $this->setOption(self::kendoEditorConfigurationOption, $config);
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

    public static function getTranslations()
    {
        return [
            "bold" => ___("Bold", "ddui-htmltext"),
            "italic" => ___("Italic", "ddui-htmltext"),
            "underline" => ___("Underline", "ddui-htmltext"),
            "strikethrough" => ___("Strikethrough", "ddui-htmltext"),
            "superscript" => ___("Superscript", "ddui-htmltext"),
            "subscript" => ___("Subscript", "ddui-htmltext"),
            "justifyCenter" => ___("Center text", "ddui-htmltext"),
            "justifyLeft" => ___("Align text left", "ddui-htmltext"),
            "justifyRight" => ___("Align text right", "ddui-htmltext"),
            "justifyFull" => ___("Justify", "ddui-htmltext"),
            "insertUnorderedList" => ___("Insert unordered list", "ddui-htmltext"),
            "insertOrderedList" => ___("Insert ordered list", "ddui-htmltext"),
            "indent" => ___("Indent", "ddui-htmltext"),
            "outdent" => ___("Outdent", "ddui-htmltext"),
            "createLink" => ___("Insert hyperlink", "ddui-htmltext"),
            "unlink" => ___("Remove hyperlink", "ddui-htmltext"),
            "insertImage" => ___("Insert image", "ddui-htmltext"),
            "insertFile" => ___("Insert file", "ddui-htmltext"),
            "insertHtml" => ___("Insert HTML", "ddui-htmltext"),
            "viewHtml" => ___("View HTML", "ddui-htmltext"),
            "fontName" => ___("Select font family", "ddui-htmltext"),
            "fontNameInherit" => ___("(inherited font)", "ddui-htmltext"),
            "fontSize" => ___("Select font size", "ddui-htmltext"),
            "fontSizeInherit" => ___("(inherited size)", "ddui-htmltext"),
            "formatBlock" => ___("Format", "ddui-htmltext"),
            "formatting" => ___("Format", "ddui-htmltext"),
            "formattingItems" => [
                "h1" => ___("Heading 1", "ddui-htmltext"),
                "h2" => ___("Heading 2", "ddui-htmltext"),
                "h3" => ___("Heading 3", "ddui-htmltext"),
                "pre" => ___("Preformatted", "ddui-htmltext"),
                "p" => ___("Paragraph", "ddui-htmltext")
            ],
            "foreColor" => ___("Text color", "ddui-htmltext"),
            "backColor" => ___("Background color", "ddui-htmltext"),
            "style" => ___("Styles", "ddui-htmltext"),
            "emptyFolder" => ___("Empty Folder", "ddui-htmltext"),
            "uploadFile" => ___("Upload", "ddui-htmltext"),
            "overflowAnchor" => ___("More tools", "ddui-htmltext"),
            "orderBy" => ___("Arrange by=>", "ddui-htmltext"),
            "orderBySize" => ___("Size", "ddui-htmltext"),
            "orderByName" => ___("Name", "ddui-htmltext"),
            "invalidFileType" => ___(
                "The selected file \"{0}\" is not valid. Supported file types are {1}.",
                "ddui-htmltext"
            ),
            "deleteFile" => ___("Are you sure you want to delete \"{0}\"?", "ddui-htmltext"),
            "overwriteFile" => ___(
                "A file with name \"{0}\" already exists in the current directory. Do you want to overwrite it?",
                "ddui-htmltext"
            ),
            "directoryNotFound" => ___("A directory with this name was not found.", "ddui-htmltext"),
            "imageWebAddress" => ___("Web address", "ddui-htmltext"),
            "imageAltText" => ___("Alternate text", "ddui-htmltext"),
            "imageWidth" => ___("Width (px)", "ddui-htmltext"),
            "imageHeight" => ___("Height (px)", "ddui-htmltext"),
            "fileWebAddress" => ___("Web address", "ddui-htmltext"),
            "fileTitle" => ___("Title", "ddui-htmltext"),
            "linkWebAddress" => ___("Web address", "ddui-htmltext"),
            "linkText" => ___("Text", "ddui-htmltext"),
            "linkToolTip" => ___("ToolTip", "ddui-htmltext"),
            "linkOpenInNewWindow" => ___("Open link in new window", "ddui-htmltext"),
            "dialogUpdate" => ___("Update", "ddui-htmltext"),
            "dialogInsert" => ___("Insert", "ddui-htmltext"),
            "dialogButtonSeparator" => ___("or", "ddui-htmltext"),
            "dialogCancel" => ___("Cancel", "ddui-htmltext"),
            "cleanFormatting" => ___("Clean formatting", "ddui-htmltext"),
            "createTable" => ___("Create table", "ddui-htmltext"),
            "addColumnLeft" => ___("Add column on the left", "ddui-htmltext"),
            "addColumnRight" => ___("Add column on the right", "ddui-htmltext"),
            "addRowAbove" => ___("Add row above", "ddui-htmltext"),
            "addRowBelow" => ___("Add row below", "ddui-htmltext"),
            "deleteRow" => ___("Delete row", "ddui-htmltext"),
            "deleteColumn" => ___("Delete column", "ddui-htmltext"),
            "dialogOk" => ___("Ok", "ddui-htmltext"),
            "tableWizard" => ___("Table Wizard", "ddui-htmltext"),
            "tableTab" => ___("Table", "ddui-htmltext"),
            "cellTab" => ___("Cell", "ddui-htmltext"),
            "accessibilityTab" => ___("Accessibility", "ddui-htmltext"),
            "caption" => ___("Caption", "ddui-htmltext"),
            "summary" => ___("Summary", "ddui-htmltext"),
            "width" => ___("Width", "ddui-htmltext"),
            "height" => ___("Height", "ddui-htmltext"),
            "units" => ___("Units", "ddui-htmltext"),
            "cellSpacing" => ___("Cell Spacing", "ddui-htmltext"),
            "cellPadding" => ___("Cell Padding", "ddui-htmltext"),
            "cellMargin" => ___("Cell Margin", "ddui-htmltext"),
            "alignment" => ___("Alignment", "ddui-htmltext"),
            "background" => ___("Background", "ddui-htmltext"),
            "cssClass" => ___("CSS Class", "ddui-htmltext"),
            "id" => ___("ID", "ddui-htmltext"),
            "border" => ___("Border", "ddui-htmltext"),
            "borderStyle" => ___("Border Style", "ddui-htmltext"),
            "collapseBorders" => ___("Collapse borders", "ddui-htmltext"),
            "wrapText" => ___("Wrap text", "ddui-htmltext"),
            "associateCellsWithHeaders" => ___("Associate cells with headers", "ddui-htmltext"),
            "alignLeft" => ___("Align Left", "ddui-htmltext"),
            "alignCenter" => ___("Align Center", "ddui-htmltext"),
            "alignRight" => ___("Align Right", "ddui-htmltext"),
            "alignLeftTop" => ___("Align Left Top", "ddui-htmltext"),
            "alignCenterTop" => ___("Align Center Top", "ddui-htmltext"),
            "alignRightTop" => ___("Align Right Top", "ddui-htmltext"),
            "alignLeftMiddle" => ___("Align Left Middle", "ddui-htmltext"),
            "alignCenterMiddle" => ___("Align Center Middle", "ddui-htmltext"),
            "alignRightMiddle" => ___("Align Right Middle", "ddui-htmltext"),
            "alignLeftBottom" => ___("Align Left Bottom", "ddui-htmltext"),
            "alignCenterBottom" => ___("Align Center Bottom", "ddui-htmltext"),
            "alignRightBottom" => ___("Align Right Bottom", "ddui-htmltext"),
            "alignRemove" => ___("Remove Alignment", "ddui-htmltext"),
            "columns" => ___("Columns", "ddui-htmltext"),
            "rows" => ___("Rows", "ddui-htmltext"),
            "selectAllCells" => ___("Select All Cells", "ddui-htmltext"),
            "print" => ___("Print", "ddui-htmltext"),
        ];
    }
}
