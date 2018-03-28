<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;
use \SmartStructure\Attributes\GROUP as myAttributes;

class GroupEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::grp_hasmail)->setDisplay('bool');
        $options->enum(myAttributes::grp_hasmail)->displayDeleteButton(false);
        return $options;
    }

}
