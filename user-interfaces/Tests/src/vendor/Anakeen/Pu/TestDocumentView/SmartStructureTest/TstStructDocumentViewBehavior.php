<?php

namespace Anakeen\Pu\TestDocumentView\SmartStructureTest;

use Anakeen\Pu\TestDocumentView\SmartStructureTest\Render\TstDocumentViewEditRender;
use Anakeen\Ui\DefaultView;
use Anakeen\Ui\IRenderConfigAccess;
use Anakeen\Ui\RenderConfigManager;

class TstStructDocumentViewBehavior extends \Anakeen\SmartElement implements IRenderConfigAccess
{
    public function registerHooks()
    {
        parent::registerHooks();
    }

    public function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        switch ($mode) {
            case RenderConfigManager::CreateMode;
            case RenderConfigManager::EditMode;
                return new TstDocumentViewEditRender();
                break;
            case RenderConfigManager::ViewMode;
                return new DefaultView();
                break;
        }
    }
}
