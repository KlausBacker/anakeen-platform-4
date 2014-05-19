<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

interface RenderConfig {
    
    const editType = "edit";
    const viewType = "view";
    public function getLabel();
    /**
     * @return string
     */
    public function getDocumentTemplate();
    /**
     * @return string[] list of css url
     */
    public function getCssReferences();
    /**
     * @return string[] list of js url
     */
    public function getJsReferences();
    /**
     * @return array set of indexed template
     */
    public function getTemplates();
    /**
     * @return array deafault render configuration options
     */
    public function getOptions();
    /**
     * return "view" or "edit"
     * @return string
     */
    public function getType();
    /**
     * @param \Doc $document Document object instance
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Doc $document);
}
