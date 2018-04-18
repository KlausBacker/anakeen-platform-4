<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Timer\Render;

use Anakeen\Ui\DefaultConfigViewRender;

class TimerViewRender extends DefaultConfigViewRender
{
    /**
     * @param \Anakeen\Core\Internal\SmartElement $document Document instance
     *
     * @return \Dcp\Ui\RenderOptions
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }
}
