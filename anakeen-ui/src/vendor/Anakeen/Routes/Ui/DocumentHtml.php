<?php
/*
 * @author Anakeen
*/

namespace Anakeen\Routes\Ui;

use Anakeen\Core\Utils\Gettext;
use Anakeen\Router\Exception;
use Anakeen\Core\DocManager;

/**
 * Class DocumentHtml
 * @note Used by route : GET /api/v2/documents/{docid}.html
 * @note Used by route : GET /api/v2/documents/{docid}/revisions/{revisions}.html
 * @note Used by route : GET /api/v2/documents/{docid}/views/{view}.html
 * @note Used by route : GET /api/v2/documents/{docid}/revisions/{revisions}/views/{view}.html
 * @package Anakeen\Routes\Ui
 */
class DocumentHtml
{
    protected $viewId = "!defaultConsultation";
    protected $revision = -1;

    /**
     * Send Document Html page
     *
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param array $args
     * @return \Slim\Http\response
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $resourceId = $args["docid"];

        if (isset($args["revision"])) {
            $this->revision = $args["revision"];
        }
        if (isset($args["view"])) {
            $this->viewId = $args["view"];
        }
        if ($resourceId === "0") {
            // Special case to load HTML page without documents
            $resourceId = false;
        }
        $html= $this->view($resourceId, $this->viewId, $this->revision);
        return $response->write($html);
    }

    /**
     * @param string|bool $initid
     * @param string $viewId
     * @param int $revision
     *
     * @return string
     * @throws Exception
     */
    public function view($initid, $viewId = "!defaultConsultation", $revision = -1)
    {
        if (!is_numeric($revision)) {
            if (!preg_match('/^state:(.+)$/', $revision, $regStates)) {
                throw new Exception(sprintf(Gettext::___("Revision \"%s\" must be a number or a state reference", "ddui"), $revision));
            }
        }

        $modeDebug = \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("DOCUMENT", "MODE_DEBUG");
        if ($modeDebug !== "FALSE") {
            $layout = new \Layout("DOCUMENT/Layout/debug/view.html");
        } else {
            $layout = new \Layout("DOCUMENT/Layout/prod/view.html");
        }
        $layout->set("BASEURL", self::getBaseUrl());
        $layout->set("NOTIFICATION_DELAY", \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("DOCUMENT", "NOTIFICATION_DELAY"));
        $layout->set("notificationLabelMore", Gettext::___("See more ...", "ddui:notification"));
        $layout->set("notificationTitleMore", Gettext::___("Notification", "ddui:notification"));
        $layout->set("messages", "{}");
        if ($initid !== false) {
            $doc = DocManager::getDocument($initid);
            if (!$doc) {
                $e = new Exception(sprintf(Gettext::___("Document identifier \"%s\"not found", "ddui"), $initid));
                $e->setHttpStatus("404", "Document not found");
                throw $e;
            }

            if ($viewId !== DocumentView::defaultViewCreationId && $viewId !== DocumentView::coreViewCreationId) {
                $err = $doc->control("view");
                if ($err) {
                    $e = new Exception(sprintf(Gettext::___("Access not granted for document #%s", "ddui"), $initid));
                    $e->setHttpStatus("403", "Forbidden");
                    throw $e;
                }
            } else {
                $err = $doc->control("icreate");
                if ($err) {
                    $e = new Exception(sprintf(Gettext::___("Access not granted to create \"%s\" document", "ddui"), $doc->getTitle()));
                    $e->setHttpStatus("403", "Forbidden");
                    throw $e;
                }
            }
            DocManager::cache()->addDocument($doc);
            $otherParameters = $_GET;

            unset($otherParameters["initid"]);

            if (is_numeric($initid)) {
                $initid = intval($initid);
            }
            //merge other parameters
            $viewInformation = ["initid" => $initid, "revision" => $revision, "viewId" => $viewId];

            $viewInformation = array_merge($viewInformation, $otherParameters);

            if (preg_match('/^state:(.+)$/', $revision, $regStates)) {
                $viewInformation["revision"] = array(
                    "state" => $regStates[1]
                );
            }
            if (!empty($viewInformation["customClientData"])) {
                $viewInformation["customClientData"] = json_decode($viewInformation["customClientData"], true);
                if ($viewInformation["customClientData"] === null) {
                    throw new Exception("Parameter \"customClientData\" must be json encoded");
                }
            }

            $layout->set("viewInformation", \Dcp\Ui\JsonHandler::encodeForHTML($viewInformation));
        } else {
            $layout->set("viewInformation", \Dcp\Ui\JsonHandler::encodeForHTML(false));
        }
        $layout->set("messages", $this->getWarningMessages());
        $render = new \Dcp\Ui\RenderDefault();

        $version = \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("CORE", "WVERSION");

        $layout->set("ws", $version);
        $cssRefs = $render->getCssReferences();
        $css = array();
        foreach ($cssRefs as $key => $path) {
            $css[] = array(
                "key" => $key,
                "path" => $path
            );
        }
        $layout->eSetBlockData("CSS", $css);
        $require = $render->getRequireReference();
        $js = array();
        foreach ($require as $key => $path) {
            $js[] = array(
                "key" => $key,
                "path" => $path
            );
        }
        $layout->eSetBlockData("JS", $js);
        return $layout->gen();
    }

    protected function getWarningMessages()
    {
        global $action;
        $warnings = $action->parent->getWarningMsg();
        $messages = [];
        foreach ($warnings as $warning) {
            $message = new \Anakeen\Routes\Core\Lib\ApiMessage();
            $message->contentText = $warning;
            $message->type = $message::WARNING;

            $messages[] = $message;
        }
        return json_encode($messages);
    }

    protected static function getBaseUrl()
    {
        // Use protocol relative url
        $url = sprintf("//%s", $_SERVER["SERVER_NAME"]);
        if ($_SERVER["SERVER_PORT"] !== "80") {
            $url .= sprintf(":%s", $_SERVER["SERVER_PORT"]);
        }
        if (preg_match('@^(.*)/api/v2/@', $_SERVER["REQUEST_URI"], $reg)) {
            $url .= $reg[1];
        } else {
            if (preg_match('@^(.*)/\\?@', $_SERVER["REQUEST_URI"], $reg)) {
                $url .= $reg[1];
            }
        }
        $url .= "/";
        return $url;
    }
}
