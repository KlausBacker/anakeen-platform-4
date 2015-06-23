<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class FamilyView extends RenderDefault
{
    
    public function getLabel(\Doc $document = null)
    {
        return ___("Family View", "ddui");
    }
    
    public function getType()
    {
        return IRenderConfig::viewType;
    }
    /**
     * @param \Doc $document Document object instance
     * @return BarMenu Menu configuration
     */
    public function getMenu(\Doc $document)
    {
        $menu = new BarMenu();
        
        $item = new ItemMenu("histo", ___("Historic", "UiMenu") , "#action/document.history");
        $item->setBeforeContent('<div class="fa fa-history" />');
        $menu->appendElement($item);
        
        $item = new ItemMenu("properties", ___("Properties", "UiMenu") , "#action/document.properties");
        $menu->appendElement($item);
        
        return $menu;
    }
    
    public function getTemplates(\Doc $document = null)
    {
        $templates = parent::getTemplates($document);
        
        $templates["sections"]["content"]["file"] = "DOCUMENT/IHM/views/document/family__content.mustache";
        return $templates;
    }
}
