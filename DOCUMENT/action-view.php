<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

use Dcp\HttpApi\V1\DocManager;
function view(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setText("Display document");
    
    $vId = $usage->addOptionalParameter("vid", "view identifier");
    $renderMode = $usage->addOptionalParameter("mode", "render mode", array(
        "view",
        "edit",
        "create"
    ) , "view");
    
    $documentId = $usage->addRequiredParameter("id", "document identifier", function ($id) use ($renderMode)
    {
        $doc = DocManager::getDocument($id);
        
        if (!$doc) {
            return sprintf(___("Document identifier \"%s\"not found", "ddui") , $id);
        }
        if ($renderMode === "create") {
            if (!is_a($doc, "DocFam")) {
                return sprintf(___("Document identifier \"%s\" must be a family in create mode", "ddui") , $id);
            }
        }
        DocManager::cache()->addDocument($doc);
        return '';
    });
    //$renderId = $usage->addOptionalParameter("render", "render identifier", array() , "defaultView");
    $usage->setStrictMode(true);
    $usage->verify();
    
    if ($renderMode === "create") {
        $doc = DocManager::createDocument($documentId);
        $doc->title = sprintf(___("%s Creation", "ddui") , $doc->getFamilyDocument()->getTitle());
    } else {
        $doc = DocManager::getDocument($documentId);
    }
    if (!$doc) {
        $action->exitError(sprintf(___("Document \"%s\" not found ", "ddui") , $documentId));
    }
    
    $doc->refresh();
    
    $dr = new Dcp\Ui\DocumentRender();
    
    $config = \Dcp\Ui\RenderConfigManager::getRenderConfig($renderMode, $doc, $vId);
    
    $dr->loadConfiguration($config);
    switch ($config->getType()) {
        case "view":
            $err = $doc->control("view");
            if ($err) {
                $action->exitForbidden($err);
            }
            break;

        case "edit":
            if ($renderMode === "create") {
                
                $err = $doc->control("icreate");
                $err.= $doc->control("create");
                if ($err) {
                    $action->exitForbidden($err);
                }
            } else {
                $err = $doc->canEdit();
                if ($err) {
                    $action->exitForbidden($err);
                }
            }
    }
    
    $action->lay->template = $dr->render($doc);
    $action->lay->noparse = true;
}

