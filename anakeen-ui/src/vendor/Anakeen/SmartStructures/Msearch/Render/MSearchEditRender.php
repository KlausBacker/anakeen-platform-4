<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Msearch\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use \SmartStructure\Attributes\Msearch as myAttributes;

class MSearchEditRender extends DefaultConfigEditRender
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