<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

function view(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setText("Display document");
    
    $documentId = $usage->addRequiredParameter("id", "document identifier");
    $vId = $usage->addOptionalParameter("vid", "view identifier");
    $renderMode = $usage->addOptionalParameter("mode", "render mode", array(
        "view",
        "edit",
        "create"
    ) , "view");
    //$renderId = $usage->addOptionalParameter("render", "render identifier", array() , "defaultView");
    $usage->setStrictMode(true);
    $usage->verify();
    
    $doc = Dcp\DocManager::getDocument($documentId);
    
    if (!$doc) {
        $action->exitError(sprintf(___("Document \"%s\" not found ", "ddui") , $documentId));
    }
    
    $doc->refresh();
    
    $dr = new Dcp\Ui\DocumentRender();
    
    $config = \Dcp\Ui\RenderConfigManager::getRenderConfig($renderMode, $doc, $vId);
    
    $dr->loadConfiguration($config);
    if ($config->getType() === "edit") {
        $err = $doc->canEdit();
        if ($err) {
            $action->exitError($err);
        }
    } else {
        $err = $doc->control("view");
        if ($err) {
            $action->exitError($err);
        }
    }
    $action->lay->template = $dr->render($doc);
    $action->lay->noparse = true;
}

