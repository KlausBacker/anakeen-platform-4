<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

use Dcp\HttpApi\V1\DocManager\DocManager;
use Dcp\HttpApi\V1\Etag\Manager as EtagManager;

function view(Action & $action)
{

    $usage = new ActionUsage($action);
    $usage->setText("Display document");


    //For compat only => use id as initid
    $id = $usage->addOptionalParameter(
        "id", "document identifier", function ($initid) {
        $doc = DocManager::getDocument($initid);

        if (!$doc) {
            return sprintf(
                ___("Document identifier \"%s\"not found", "ddui"), $initid
            );
        }
        DocManager::cache()->addDocument($doc);
        return '';
    }
        , false
    );

    $initid = $usage->addOptionalParameter(
        "initid", "document identifier", function ($initid) {
        $doc = DocManager::getDocument($initid);

        if (!$doc) {
            return sprintf(
                ___("Document identifier \"%s\"not found", "ddui"), $initid
            );
        }
        DocManager::cache()->addDocument($doc);
        return '';
    }
        , false
    );

    if ($initid === false) {
        $id = $initid;
    }

    //For compat only : use vid as old key
    $viewId = $usage->addOptionalParameter(
        "vid", "view identifier", array(), "!defaultConsultation"
    );

    $viewId = $usage->addOptionalParameter(
        "viewId", "view identifier", array(), "!defaultConsultation"
    );

    $revision = $usage->addOptionalParameter(
        "revision", "revision number", function ($revision) {
        if (!is_numeric($revision)) {
            return sprintf(
                ___("Revision \"%s\" must be a number ", "ddui"), $revision
            );
        }
        return '';
    }
        , -1
    );

    $usage->setStrictMode(false);
    $usage->verify();

    if ($initid === false) {
        //Boot the init page in void mode (used by offline project)
        $etag = md5(
            sprintf(
                "%s : %s", \ApplicationParameterManager::getParameterValue(
                "CORE", "WVERSION"
            ), \ApplicationParameterManager::getScopedParameterValue(
                "CORE_LANG"
            )
            )
        );
        $etagManager = new EtagManager();
        if ($etagManager->verifyCache($etag)) {
            $etagManager->generateNotModifiedResponse($etag);
            $action->lay->template = "";
            $action->lay->noparse = true;
            header("Cache-Control:");
            return;
        }
        $action->lay->set(
            "viewInformation", Dcp\Ui\JsonHandler::encodeForHTML(false)
        );
        $etagManager->generateResponseHeader($etag);
    } else {

        $otherParameters = $_GET;

        unset($otherParameters["initid"]);

        //merge other parameters
        $viewInformation = [
            "initid" => $initid,
            "revision" => $revision,
            "viewId" => $viewId
        ];

        $viewInformation = array_merge($viewInformation, $otherParameters);

        $action->lay->set(
            "viewInformation",
            Dcp\Ui\JsonHandler::encodeForHTML($viewInformation)
        );
    }

    $render = new \Dcp\Ui\RenderDefault();

    $version = \ApplicationParameterManager::getParameterValue(
        "CORE", "WVERSION"
    );

    $action->lay->set("ws", $version);
    $cssRefs = $render->getCssReferences();
    $css = array();
    foreach ($cssRefs as $key => $path) {
        $css[] = array(
            "key" => $key,
            "path" => $path
        );
    }
    $action->lay->eSetBlockData("CSS", $css);
    $require = $render->getRequireReference();
    $js = array();
    foreach ($require as $key => $path) {
        $js[] = array(
            "key" => $key,
            "path" => $path
        );
    }
    $action->lay->eSetBlockData("JS", $js);
}

