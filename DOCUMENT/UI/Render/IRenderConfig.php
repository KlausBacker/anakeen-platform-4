<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

interface IRenderConfig {
    
    const editType = "edit";
    const viewType = "view";
    /**
     * @param \Doc $document Document object instance
     * Get the label of this view
     * @return string the text label
     */
    public function getLabel(\Doc $document = null);
    /**
     * @param \Doc $document Document object instance
     * @return string[] list of css url
     */
    public function getCssReferences(\Doc $document = null);
    /**
     * @param \Doc $document Document object instance
     * @return string[] list of js url
     */
    public function getJsReferences(\Doc $document = null);
    /**
     * @param \Doc $document Document object instance
     * @return array set of indexed template
     */
    public function getTemplates(\Doc $document = null);
    /**
     * @param \Doc $document Document object instance
     * @return RenderOptions deafault render configuration options
     */
    public function getOptions(\Doc $document);
    /**
     * @param \Doc $document Document object instance
     * @return RenderAttributeVisibilities new attribute visibilities
     */
    public function getVisibilities(\Doc $document);
    /**
     * @param \Doc $document Document object instance
     * @return RenderAttributeNeeded new mandatory attribute
     */
    public function getNeeded(\Doc $document);
    /**
     * @param \Doc $document Document instance
     * @return DocumentTemplateContext get template controller
     */
    public function getContextController(\Doc $document);
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
    /**
     * Get custom data to transmit to client document controller
     * @param \Doc $document Document object instance
     * @return mixed
     */
    public function getCustomServerData(\Doc $document);
    /**
     * Retrieve some custom data
     * @param \Doc $document Document object instance
     * @param mixed $data data provided by client
     *
     * @return mixed
     */
    public function setCustomClientData(\Doc $document, $data);
}
