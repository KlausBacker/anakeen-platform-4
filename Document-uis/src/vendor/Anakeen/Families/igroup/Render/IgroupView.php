<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\Ui;

use \Dcp\AttributeIdentifiers\IGROUP as myAttributes;
use Dcp\Ui\ButtonOptions;
use Dcp\Ui\CreateDocumentOptions;

class IgroupViewRender extends defaultConfigViewRender
{
    public function getOptions(\Doc $document)
    {
        $options = parent::getOptions($document);
        return $options;
    }
}
