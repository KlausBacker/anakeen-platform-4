<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Mask\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\SmartStructures\Mask\Routes\MaskVisibilities;
use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use \SmartStructure\Fields\Mask as myAttributes;
use SmartStructure\Mask;

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

    public function getTemplates(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $templates = parent::getTemplates($document);
        $templates["sections"]["content"] = array(
            "file" => __DIR__.'/MaskEdit.mustache'
        );
        return $templates;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $document = null)
    {
        $js = parent::getJsReferences();
        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $js["maskEdit"] = $path["MaskEdit"]["js"];

        return $js;
    }

    public function getCustomServerData(SmartElement $smartElement)
    {
        $data = parent::getCustomServerData($smartElement);
        $visLabel = new MaskVisibilities();
        $data["VISIBILITIES_LABEL"] = $visLabel->getVisibilityLabels();

        return $data;
    }
}
