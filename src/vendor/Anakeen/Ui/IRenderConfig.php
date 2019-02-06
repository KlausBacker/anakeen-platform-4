<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Ui;

use Anakeen\Routes\Core\Lib\ApiMessage;

interface IRenderConfig
{

    const editType = "edit";
    const viewType = "view";

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     *                                                      Get the label of this view
     * @return string the text label
     */
    public function getLabel(\Anakeen\Core\Internal\SmartElement $document = null);

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     * @return string[] list of css url
     */
    public function getCssReferences(\Anakeen\Core\Internal\SmartElement $document = null);

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     * @return string[] list of js url
     */
    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null);

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     * @return array set of indexed template
     */
    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null);

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     * @return RenderOptions default render configuration options
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document) : RenderOptions;

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     * @param \SmartStructure\Mask|null           $mask mask to apply
     * @return RenderAttributeVisibilities new attribute visibilities
     */
    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities;

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     * @return RenderAttributeNeeded new mandatory attribute
     */
    public function getNeeded(\Anakeen\Core\Internal\SmartElement $document): RenderAttributeNeeded;

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     * @return DocumentTemplateContext get template controller
     */
    public function getContextController(\Anakeen\Core\Internal\SmartElement $document) : DocumentTemplateContext;

    /**
     * return "view" or "edit"
     * @return string
     */
    public function getType();

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Anakeen\Core\Internal\SmartElement $document) : BarMenu;

    /**
     * Get custom data to transmit to client document controller
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     * @return mixed
     */
    public function getCustomServerData(\Anakeen\Core\Internal\SmartElement $document);

    /**
     * Retrieve some custom data
     * @param \Anakeen\Core\Internal\SmartElement $document Document object instance
     * @param mixed                               $data     data provided by client
     *
     * @return mixed
     */
    public function setCustomClientData(\Anakeen\Core\Internal\SmartElement $document, $data);

    /**
     * Defined special render etag
     * Return empty string if no special tag, null for always invalid tag
     * Else return a string
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return string|null
     */
    public function getEtag(\Anakeen\Core\Internal\SmartElement $document);

    /**
     * Return some messages to be displayed in user interface
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return ApiMessage[]
     */
    public function getMessages(\Anakeen\Core\Internal\SmartElement $document);
}
