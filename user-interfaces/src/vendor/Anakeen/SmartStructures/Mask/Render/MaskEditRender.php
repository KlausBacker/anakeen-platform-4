<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Mask\Render;

use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\RenderOptions;
use \SmartStructure\Fields\Mask as myAttributes;

class MaskEditRender extends DefaultConfigEditRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
    {
        $options = parent::getOptions($document);
        /*$options->arrayAttribute(myAttributes::msk_t_contain)->disableRowAdd(true);
        $options->arrayAttribute(myAttributes::msk_t_contain)->disableRowMove(true);
        $options->arrayAttribute(myAttributes::msk_t_contain)->disableRowDel(true);*/
        return $options;
    }
}
