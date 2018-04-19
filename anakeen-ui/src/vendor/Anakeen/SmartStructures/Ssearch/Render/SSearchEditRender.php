<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Ssearch\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use \SmartStructure\Attributes\Ssearch as myAttributes;

class SSearchEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::gui_isrss)->setDisplay('bool');
        $options->enum(myAttributes::se_memo)->setDisplay('bool');
        $options->enum(myAttributes::gui_isrss)->displayDeleteButton(false);
        $options->enum(myAttributes::se_memo)->displayDeleteButton(false);
        return $options;
    }
}