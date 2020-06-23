<?php

namespace Anakeen\SmartStructures\Mail\Render;

use Anakeen\Core\Internal\SmartElement;

class MailAccess implements \Anakeen\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @param SmartElement $document
     * @return \Anakeen\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, SmartElement $document)
    {
        switch ($mode) {
            case \Anakeen\Ui\RenderConfigManager::CreateMode:
            case \Anakeen\Ui\RenderConfigManager::EditMode:
                return new MailEditRender();
        }

        return null;
    }
}
