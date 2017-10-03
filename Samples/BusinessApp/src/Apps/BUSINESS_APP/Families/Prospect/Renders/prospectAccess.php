<?php
namespace Sample\BusinessApp\Renders;

class ProspectAccess implements \Dcp\Ui\IRenderConfigAccess
{
    /**
     * @param string $mode
     * @return \Dcp\Ui\IRenderConfig
     */
    public function getRenderConfig($mode, \Doc $document)
    {
        switch ($mode) {
            case \Dcp\Ui\RenderConfigManager::CreateMode:
                return new ProspectCreate($this);
            case \Dcp\Ui\RenderConfigManager::EditMode:
                return new ProspectEdit($this);
            case \Dcp\Ui\RenderConfigManager::ViewMode:

                return new ProspectView($this);
        }
        
        return null;
    }
}
