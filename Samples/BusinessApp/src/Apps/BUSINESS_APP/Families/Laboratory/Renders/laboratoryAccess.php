<?php
namespace Sample\BusinessApp\Renders;

class LaboratoryAccess implements \Dcp\Ui\IRenderConfigAccess
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
                return new LaboratoryEdit($this);
            case \Dcp\Ui\RenderConfigManager::ViewMode:

                return new LaboratoryView($this);
        }
        
        return null;
    }
}
