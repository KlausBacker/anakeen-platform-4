<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Igroup\Render;

use Anakeen\Ui\DefaultConfigViewRender;
use \SmartStructure\Attributes\IGROUP as myAttributes;
use Dcp\Ui\ButtonOptions;
use Dcp\Ui\CreateDocumentOptions;

class IgroupViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }
}
