<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace {
    
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
         * @see Dcp\Ui\DocumentTemplate
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
         * @see \Dcp\Ui\RenderConfig::getCssReferences
         */
        const UI0010 = 'Render css reference must be an array "%s" found :';
        /**
         * @errorCode the implement method must return an array
         * @see \Dcp\Ui\RenderConfig::geJsReferences
         */
        const UI0011 = 'Render js reference must be an array "%s" found :';
        /**
         * @errorCode the menu item not exist
         * @see \Dcp\Ui\BarMenu::insertBefore
         */
        const UI0100 = 'Menu insertBefore : Element index \"%s\" not exists';
        /**
         * @errorCode the menu item not exist
         * @see \Dcp\Ui\BarMenu::insertAfter
         */
        const UI0101 = 'Menu insertAfter : Element index \"%s\" not exists';
        /**
         * @errorCode the attribte not exists
         * @see \Dcp\Ui\RenderAttributeVisibilities::setVisibility
         */
        const UI0102 = 'setVisibility : Attribute \"%s\" not exists for "%s" document';
        /**
         * @errorCode Visibility must be one of R,W,O,I,S,H
         * @see \Dcp\Ui\RenderAttributeVisibilities::setVisibility
         */
        const UI0103 = 'setVisibility : Visibility "%s" not correct. Allowed are "%s';
        /**
         * @errorCode the menu item not exist
         */
        const UI0200 = 'Value "%s" for Enum option "display" is invalid : allowed are : %s';
        /**
         * @errorCode the menu item not exist
         */
        const UI0201 = 'Value "%s" for attribute option "labelPosition" is invalid : allowed are : %s';
        /**
         * for beautifier
         */
        private function _bo()
        {
            if (true) return;
        }
    }
}
namespace Dcp\Ui {
    class Exception extends \Dcp\Exception
    {
        /**
         * for beautifier
         */
        private function _bo()
        {
            if (true) return;
        }
    }
}
