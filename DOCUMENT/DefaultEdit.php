<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class DefaultEdit extends RenderDefault
{
    
    public function getLabel()
    {
        return _("Default Edit");
    }
    
    public function getType()
    {
        return RenderConfig::editType;
    }
    /**
     * @param \Doc $document Document object instance
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Doc $document)
    {
        $menu = new BarMenu();
        if ($document->id > 0) {
            $menu->appendElement(new ItemMenu("save", ___("Save", "UiMenu") , "#save/{{document.properties.id}}"));
        } else {
            $menu->appendElement(new ItemMenu("save", ___("Create", "UiMenu") , "#create/{{document.properties.id}}"));
        }
        $menu->appendElement(new ItemMenu("cancel", ___("Cancel", "UiMenu") , "#cancel/{{document.properties.id}}"));
        
        return $menu;
    }
}
