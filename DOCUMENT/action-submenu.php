<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

use Dcp\HttpApi\V1\DocManager\DocManager;
function submenu(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setText("get submenu document");
    
    $documentId = $usage->addRequiredParameter("id", "document identifier");
    $menuId = $usage->addRequiredParameter("menu", "sub menu identifier");
    $renderId = $usage->addOptionalParameter("render", "render identifier", array() , "defaultView");
    $usage->setStrictMode(false);
    $usage->verify();
    
    $doc = DocManager::getDocument($documentId);
    
    if (!$doc) {
        $action->exitError(sprintf(___("Document \"%s\" not found ", "ddui") , $documentId));
    }
    $err = $doc->control("view");
    if ($err) {
        $action->exitError($err);
    }
    
    $config = Dcp\Ui\Utils::getRenderConfigObject($renderId);
    
    $menu = $config->getMenu($doc);
    /**
     * @var \Dcp\Ui\DynamicMenu $element
     */
    $element = $menu->getElement($menuId);
    
    if (!$element) {
        $action->exitError(sprintf(___("Menu id \"%s\" not found ", "ddui") , $menuId));
    }
    
    $action->lay->template = json_encode($element->getContent());
    $action->lay->noparse = true;
    header('Content-Type: application/json');
}
