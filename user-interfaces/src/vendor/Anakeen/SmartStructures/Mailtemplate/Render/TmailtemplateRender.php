<?php

namespace Anakeen\SmartStructures\Mailtemplate\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Ui\UIGetAssetPath;

trait TmailtemplateRender
{
    public function getCommonJsReference(SmartElement $se, array $js)
    {
        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "prod");
        $js["mailTemplateRender"] = $path["MailTemplateRender"]["js"];
        return $js;
    }
}
