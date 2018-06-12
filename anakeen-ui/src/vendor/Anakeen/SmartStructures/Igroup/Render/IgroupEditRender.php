<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Igroup\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use \SmartStructure\Attributes\Igroup as myAttributes;
use Dcp\Ui\ButtonOptions;
use Dcp\Ui\CreateDocumentOptions;

class IgroupEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::grp_hasmail)->setDisplay('bool');
        $options->enum(myAttributes::grp_hasmail)->displayDeleteButton(false);
        return $options;
    }
}