<?php


use Dcp\Ui\DocidRenderOptions;

class ErrorCodeUI
{
    /**
     * @errorCode The render is not found
     */
    const UI0001 = 'Render configuration file not found: %s';
    /**
     * @errorCode The render not conatint a json object
     */
    const UI0002 = 'Render configuration file is not a valid json : %s';
    /**
     * @errorCode Extract "renderer/documentTemplate" of render configuration
     */
    const UI0003 = 'Render template file not set : %s';
    /**
     * @errorCode Extract "renderer/documentTemplate" of render configuration
     */
    const UI0004 = 'Render template file not found : %s';
    /**
     * @errorCode File use in DocumentTemplate class not found
     * @see       Dcp\Ui\DocumentTemplate
     */
    const UI0005 = 'Document template file not found : %s';
    /**
     * @errorCode THe template fril of section is not defined in render configuration
     */
    const UI0006 = 'Document template file for "%s" section is not defined';
    /**
     * @errorCode THe template fril of section is not defined in render configuration
     */
    const UI0007 = 'Document template file for "%s" is not found : "%s"';
    /**
     * @errorCode Cannot get json render file
     */
    const UI0008 = 'Render configuration file "%s" is not found :';
    /**
     * @errorCode the implement method must return an array
     * @see       \Dcp\Ui\RenderConfig::getCssReferences
     */
    const UI0010 = 'Render css reference must be an array "%s" found :';
    /**
     * @errorCode the implement method must return an array
     * @see       \Dcp\Ui\RenderConfig::getJsReferences
     */
    const UI0011 = 'Render js reference must be an array "%s" found :';
    /**
     * @errorCode the implement method must return an array
     * @see       \Dcp\Ui\RenderConfig::getTemplates
     */
    const UI0012 = 'Method "%s::getTemplate" must return array';
    /**
     * @errorCode the implement method must return an Dcp\Ui\IRenderOptions object
     * @see       \Dcp\Ui\RenderConfig::getOptions
     */
    const UI0013 = 'Method "%s::getOptions" must return \Dcp\Ui\IRenderOptions';
    /**
     * @errorCode the menu item not exist
     * @see       \Dcp\Ui\BarMenu::insertBefore
     */
    const UI0100 = 'Menu insertBefore : Element index "%s" not exists';
    /**
     * @errorCode the menu item not exist
     * @see       \Dcp\Ui\BarMenu::insertAfter
     */
    const UI0101 = 'Menu insertAfter : Element index "%s" not exists';
    /**
     * @errorCode the attribte not exists
     * @see       \Dcp\Ui\RenderAttributeVisibilities::setVisibility
     */
    const UI0102 = 'setVisibility : Attribute "%s" not exists for "%s" document';
    /**
     * @errorCode Visibility must be one of R,W,O,I,S,H
     * @see       \Dcp\Ui\RenderAttributeVisibilities::setVisibility
     */
    const UI0103 = 'setVisibility : Visibility "%s" not correct. Allowed are "%s';
    /**
     * @errorCode the attribute not exists
     * @see       \Dcp\Ui\RenderAttributeNeeded::setNeeded
     */
    const UI0104 = 'setNeeded : Attribute "%s" not exists for "%s" document';
    /**
     * @errorCode Cannot apply needed on tab, frame or menu attributes, only an "normal" attribute
     * @see       \Dcp\Ui\RenderAttributeNeeded::setNeeded
     */
    const UI0105 = 'setNeeded : Attribute "%s" cannot be needed (not allowed type)  for "%s" document';
    /**
     * @errorCode Cannot apply needed an attribute wich is in an array
     * @see       \Dcp\Ui\RenderAttributeNeeded::setNeeded
     */
    const UI0106 = 'setNeeded : Attribute "%s" cannot be needed (must not be in an array)  for "%s" document';
    /**
     * @errorCode Tab can be set only on top or right
     * @see       \Dcp\Ui\TabRenderOptions::setTabPlacement
     */
    const UI0107 = 'setTabPlacement : Placement "%s" is not valid,  allowed placement are "%s" document';
    /**
     * @errorCode the menu item not exist
     */
    const UI0200 = 'Value "%s" for Enum option "display" is invalid : allowed are : %s';
    /**
     * @errorCode the menu item not exist
     */
    const UI0201 = 'Value "%s" for attribute option "labelPosition" is invalid : allowed are : %s';
    /**
     * @errorCode the cv menu cannot be extracted
     */
    const UI0202 = 'View control document  "%s" is not found : menu cannot be completed';
    /**
     * @errorCode The max lenght must be a positive number
     * @see       TextRenderOptions::setMaxLength
     */
    const UI0203 = 'Max length  "%s" is not a positive number';
    /**
     * @errorCode The line number must be a positive number
     * @see       LongtextRenderOptions::setMaxDisplayedLineNumber
     */
    const UI0204 = 'Line number  "%s" is not a positive number';
    /**
     * @errorCode Description Position must be in defined set
     * @see       CommonRenderOptions::setDescription
     */
    const UI0205 = 'Value "%s" for position option "setDescription" is invalid : allowed are : %s';
    /**
     * @errorCode Description Html text is a string
     * @see       CommonRenderOptions::setDescription
     */
    const UI0206 = 'Text must string, found "%s"';
    /**
     * @errorCode Description Expand parameter is a bool
     * @see       CommonRenderOptions::setDescription
     */
    const UI0207 = 'Expand must be a bool, found "%s"';
    /**
     * @errorCode Description Html text is a string
     * @see       CommonRenderOptions::setLinkHelp
     */
    const UI0208 = 'Document help must be a HELPPAGE document , found "%s"';
    /**
     * @errorCode invalid value for orderBy enum option
     */
    const UI0209 = 'Value "%s" for Enum option "orderBy" is invalid : allowed are : %s';
    /**
     * @errorCode The options to display docid are restricted
     * @see       DocidRenderOptions::setDisplay
     */
    const UI0210 = 'Value "%s" for Docid option "display" is invalid : allowed are : %s';
    /**
     * @errorCode The family must be indicated to display creation form
     * @see       DocidRenderOptions::addCreateDocumentButton
     */
    const UI0211 = 'Family name is required in addCreateDocumentButton option';
    /**
     * @errorCode The size can be a number or a geometry (300, x400, 200x120)
     */
    const UI0212 = 'Incorrect size "%s" : must be a number or 2 numbers separate by x';
    /**
     * @errorCode the collapse may be true, false ou null
     */
    const UI0213 = 'Value "%s" for Frame option "collapse" is invalid : allowed are : %s';
    /**
     * @errorCode the collapse may be true, false ou null
     */
    const UI0214 = 'Value "%s" for Array option "collapse" is invalid : allowed are : %s';
    /**
     * @errorCode Only view,edit, create are allowed
     */
    const UI0300 = 'Mode "%s" is not a valid render mode';
    /**
     * @errorCode Only document with view control can ask a special render
     */
    const UI0301 = 'view "%s" cannot be use in document "%s". It has no view control';
    /**
     * @errorCode The view control associated to document is not found
     */
    const UI0302 = 'view control document "%s" not exists. ';
    /**
     * @errorCode The view control associated to document is not a view control
     */
    const UI0303 = 'document "%s" is not a view control document. ';
    /**
     * @errorCode The view identifier require privilege
     */
    const UI0304 = 'view "%s" is not allowed for document "%s" . ';
    /**
     * @errorCode The view identifier not exists
     */
    const UI0305 = 'view "%s" is not defined in view control document "%s" . ';
    /**
     * @errorCode The render config reference set in view control is not a Render Config class
     */
    const UI0306 = 'class "%s" not implement "%s" . ';
    /**
     * @errorCode Document identifier must be a familiy identifier to  create
     */
    const UI0307 = 'Identifier "%s" must be a family identifier . ';
    /**
     * @errorCode The view identifier use core ZONE and not declare renderConfig class
     */
    const UI0308 = 'view "%s" is not valid in view control document "%s" . It use a ZONE layout';

    /**
     * @errorCode The manifest json is unknown
     */
    const UI0400 = 'The manifest for the asset "%s" is not known (%s)';

    /**
     * @errorCode The module is not known in the theme manifest (need upgrade the theme ?)
     */
    const UI0401 = 'The module %s is not known in the theme';

    /**
     * for beautifier
     */
    private function _bo()
    {
        if (true) {
            return;
        }
    }
}

