<?php

namespace Sample\BusinessApp\Renders;

/**
 * Class CommonRender
 * @package Ccfd
 * @extends \Dcp\Ui\RenderDefault
 */
trait Common {
    /**
     * @param \Doc $document
     * @return \Dcp\Ui\RenderOptions
     */
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->document()->setTabPlacement(\Dcp\Ui\DocumentRenderOptions::tabTopProportionalPlacement);

        return $options;
    }

    /**
     * @param \Doc|null $document
     * @return string[]
     */
    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences($document);
        $css["CCFDCommon"] = "./BUSINESS_APP/Families/Common/Renders/common.css";
        return $css;
    }
}