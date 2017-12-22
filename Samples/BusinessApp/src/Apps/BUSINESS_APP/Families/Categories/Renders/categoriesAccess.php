<?php
namespace Sample\BusinessApp\Renders;

class CategoriesAccess implements \Dcp\Ui\IRenderConfigAccess
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
                return new CategoriesEdit($this);
            case \Dcp\Ui\RenderConfigManager::ViewMode:

                return new CategoriesView($this);
        }
        
        return null;
    }
}
