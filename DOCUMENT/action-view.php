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
    $renderId = $usage->addOptionalParameter("render", "render identifier", array() , "defaultView");
    $usage->setStrictMode(true);
    $usage->verify();
    
    $doc = Dcp\DocManager::getDocument($documentId);
    
    if (!$doc) {
        $action->exitError(sprintf(___("Document \"%s\" not found ", "ddui") , $documentId));
    }
    $err = $doc->control("view");
    if ($err) {
        $action->exitError($err);
    }
    
    $doc->refresh();
    
    $dr = new Dcp\Ui\DocumentRender();
    
    $config = Dcp\Ui\Utils::getRenderConfigObject($renderId);
    
    $dr->loadConfiguration($config);
    if ($config->getType() === "edit") {
        $doc->applyMask(\Doc::USEMASKCVEDIT);
    } else {
        $doc->applyMask(\Doc::USEMASKCVVIEW);
    }
    $action->lay->template = $dr->render($doc);
    $action->lay->noparse = true;
}

