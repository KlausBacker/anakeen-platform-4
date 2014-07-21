<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class HtmltextRenderOptions extends CommonRenderOptions
{
    
    const type = "htmltext";
    const toolbarOption = "toolbar";
    const heightOption = "height";
    const toolbarStartupExpandedOption = "toolbarStartupExpanded";
    const languageOption = "language";
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
     * Need to precise unit like "px"
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
}
