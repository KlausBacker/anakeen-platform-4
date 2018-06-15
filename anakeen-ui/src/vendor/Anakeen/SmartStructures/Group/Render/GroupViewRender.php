<?php

namespace Anakeen\SmartStructures\Group\Render;

use Anakeen\Ui\DefaultConfigViewRender;

class GroupViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }
}
