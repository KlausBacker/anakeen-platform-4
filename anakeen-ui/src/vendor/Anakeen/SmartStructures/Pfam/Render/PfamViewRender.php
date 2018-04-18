<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Pfam\Render;

use Anakeen\Ui\DefaultConfigViewRender;

class PfamViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }
}