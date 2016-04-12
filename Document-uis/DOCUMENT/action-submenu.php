<?php
/*
 * @author Anakeen
 * @package FDL
*/

use Dcp\HttpApi\V1\DocManager\DocManager;

function submenu(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setText("get submenu document");
    
    $documentId = $usage->addRequiredParameter("initid", "document identifier");
    $menuId = $usage->addRequiredParameter("menu", "sub menu identifier");
    $vId = $usage->addOptionalParameter("viewId", "view identifier");
    $renderMode = $usage->addOptionalParameter("mode", "render mode", array(
        "view",
        "edit",
        "create"
    ) , "view");
    $usage->setStrictMode(false);
    $usage->verify();
    
    $doc = DocManager::getDocument($documentId);
    try {
        if (!$doc) {
            throw new \Dcp\Ui\Exception(sprintf(___("Document \"%s\" not found ", "ddui") , $documentId));
        }
        $err = $doc->control("view");
        if ($err) {
            throw new \Dcp\Ui\Exception($err);
        }
        
        if ($vId && $vId[0] === "!") {
            $vId = '';
        }
        
        $config = \Dcp\Ui\RenderConfigManager::getRenderConfig($renderMode, $doc, $vId);
        $menu = $config->getMenu($doc);
        /**
         * @var \Dcp\Ui\DynamicMenu $element
         */
        $element = $menu->getElement($menuId);
        if (!$element) {
            throw new \Dcp\Ui\Exception(sprintf(___("Menu id \"%s\" not found ", "ddui") , $menuId));
        }
        
        $action->lay->template = json_encode($element->getContent());
    }
    catch(Exception $e) {
        $action->lay->template = $e->getMessage();
        header("HTTP/1.0 400 Error");
    }
    $action->lay->noparse = true;
    header('Content-Type: application/json');
}
