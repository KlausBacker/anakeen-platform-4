<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;
use Dcp\AttributeIdentifiers\TIMER as myAttributes;

class TimerViewRender extends defaultConfigViewRender
{
    /**
     * @param \Doc $document Document instance
     *
     * @return \Dcp\Ui\RenderOptions
     */
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }

    public function getCssReferences(\Doc $document = null)
    {
        $css = parent::getCssReferences();
        $version = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        $css["dduiTimer"] = "DOCUMENT/Layout/Timer/timer.css?ws=" . $version;
        return $css;
    }
}
