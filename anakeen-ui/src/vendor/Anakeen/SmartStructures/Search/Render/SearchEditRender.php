<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Search\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Dcp\Ui\RenderOptions;
use \SmartStructure\Fields\Search as myAttributes;

class SearchEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::se_case)->setDisplay('vertical');
        $options->enum(myAttributes::se_trash)->setDisplay('vertical');
        $options->enum(myAttributes::se_famonly)->setDisplay('bool');
        $options->enum(myAttributes::se_famonly)->displayDeleteButton(false);
        $options->enum(myAttributes::se_acl)->setDisplay('vertical');
        $options->enum(myAttributes::se_sysfam)->setDisplay('bool');
        $options->enum(myAttributes::se_sysfam)->displayDeleteButton(false);
        return $options;
    }
}