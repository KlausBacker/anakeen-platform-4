<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
*/

use Dcp\HttpApi\V1\DocManager\DocManager;

function view(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Display document");
    //For compat only => use id as initid
    $id = $usage->addHiddenParameter("id", "document identifier");
    
    $initid = $usage->addOptionalParameter("initid", "document identifier", function ($initid)
    {
        $doc = DocManager::getDocument($initid);
        
        if (!$doc) {
            return sprintf(___("Document identifier \"%s\"not found", "ddui") , $initid);
        }
        DocManager::cache()->addDocument($doc);
        return '';
    }
    , false);
    
    if ($initid === false && $id) {
        $doc = DocManager::getDocument($id);
        if (!$doc) {
            $usage->exitError(sprintf(___("Document identifier \"%s\"not found", "ddui") , $initid));
        }
        DocManager::cache()->addDocument($doc);
        $initid = $doc->initid;
    }
    
    $viewId = $usage->addOptionalParameter("viewId", "view identifier", array() , "!defaultConsultation");
    
    $revision = $usage->addOptionalParameter("revision", "revision number", function ($revision)
    {
        if (!is_numeric($revision)) {
            if (!preg_match('/^state:(.+)$/', $revision, $regStates)) {
                return sprintf(___("Revision \"%s\" must be a number or a state reference", "ddui") , $revision);
            }
        }
        return '';
    }
    , -1);
    
    $usage->setStrictMode(false);
    $usage->verify();
    
    $viewHtml = new Dcp\Ui\Html\Document();
    
    $action->lay->template = $viewHtml->view($initid, $viewId, $revision);
    $action->lay->noparse = true;
}

