<?php
namespace Sample\BusinessApp\Renders;

class ClientAccess implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Doc $document)
    {
        switch ($mode) {
            case \Dcp\Ui\RenderConfigManager::CreateMode:
            case \Dcp\Ui\RenderConfigManager::EditMode:
                return new ClientEdit($this);
            case \Dcp\Ui\RenderConfigManager::ViewMode:

                return new ClientView($this);
        }
        
        return null;
    }
}
