<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

use \Dcp\AttributeIdentifiers\SEARCH as myAttributes;

class SearchEditRender extends defaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::gui_isrss)->setDisplay('bool');
        $options->enum(myAttributes::se_memo)->setDisplay('bool');
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