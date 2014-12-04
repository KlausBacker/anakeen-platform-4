<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

use Dcp\HttpApi\V1\DocManager\DocManager;

class FamilyView extends RenderDefault
{
    
    public function getLabel()
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
        
        $item = new ItemMenu("histo", ___("Historic", "UiMenu") , "#event/document:history");
        $item->setBeforeContent('<div class="fa fa-history" />');
        $menu->appendElement($item);
        
        $menu->appendElement(new ListMenu("advanced", ___("Advanced", "UiMenu")));
        
        $item = new ItemMenu("properties", ___("Properties", "UiMenu") , "#event/document:properties");
        $menu->getElement("advanced")->appendElement($item);
        
        $securitySubMenu = new ListMenu("security", ___("Security", "UiMenu"));
        $item = new ItemMenu("profil", ___("Profil access", "UiMenu") , "?app=FREEDOM&action=FREEDOM_GACCESS&id={{document.properties.id}}");
        $targetOption = new MenuTargetOptions();
        $targetOption->windowHeight = "400px";
        $targetOption->windowWidth = "600px";
        $item->setTarget("_dialog", $targetOption);
        $securitySubMenu->appendElement($item);
        
        $menu->getElement("advanced")->appendElement($securitySubMenu);
        
        return $menu;
    }
    
    public function getTemplates(\Doc $document = null)
    {
        $templates = parent::getTemplates($document);
        
        $templates["sections"]["content"]["file"] = "DOCUMENT/IHM/views/document/family__content.mustache";
        return $templates;
    }
}
