<?php
/*
 * @author Anakeen
*/

namespace Anakeen\Routes\Ui;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\I18nTemplateContext;
use Anakeen\Core\Utils\Gettext;
use Anakeen\Router\Exception;
use Anakeen\Core\SEManager;

/**
 * Class DocumentHtml
 * @note    Used by route : GET /api/v2/smart-elements/{docid}.html
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/revisions/{revisions}.html
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/views/{view}.html
 * @note    Used by route : GET /api/v2/smart-elements/{docid}/revisions/{revisions}/views/{view}.html
 * @package Anakeen\Routes\Ui
 */
class DocumentHtml
{
    const templateFile = __DIR__ . "/Templates/document-view.html.mustache";
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
        $html = $this->view($resourceId, $this->viewId, $this->revision);
        return $response->write($html);
    }

    public function view($initid, $viewId = "!defaultConsultation", $revision = -1)
    {
        $data = $this->getData($initid, $viewId, $revision);
        $mustache = new \Mustache_Engine();
        return $mustache->render(file_get_contents(static::templateFile), $data);
    }

    /**
     * @param string|bool $initid
     * @param string $viewId
     * @param int $revision
     *
     * @return string
     * @throws Exception
     */
    public function getData($initid, $viewId = "!defaultConsultation", $revision = -1)
    {
        if (!is_numeric($revision)) {
            if (!preg_match('/^state:(.+)$/', $revision, $regStates)) {
                throw new Exception(sprintf(Gettext::___(
                    "Revision \"%s\" must be a number or a state reference",
                    "ddui"
                ), $revision));
            }
        }

        $data = new I18nTemplateContext();
        $data["NOTIFICATION_DELAY"] = ContextManager::getParameterValue("Ui", "NOTIFICATION_DELAY");
        $data["htmlTitle"] = ___("Smart ELement", "ddui");
        $data["notificationLabelMore"] = ___("See more ...", "ddui:notification");
        $data["notificationTitleMore"] = ___("Notification", "ddui:notification");
        $data["messages"] = "{}";
        if ($initid !== false) {
            $doc = SEManager::getDocument($initid);
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
                $data["htmlTitle"] = $doc->getTitle();
            } else {
                $err = $doc->control("icreate");
                if ($err) {
                    $e = new Exception(sprintf(
                        Gettext::___("Access not granted to create \"%s\" document", "ddui"),
                        $doc->getTitle()
                    ));
                    $e->setHttpStatus("403", "Forbidden");
                    throw $e;
                }
            }
            SEManager::cache()->addDocument($doc);
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

            $data["viewInformation"] = \Anakeen\Ui\JsonHandler::encodeForHTML($viewInformation);
        } else {
            $data["viewInformation"] = \Anakeen\Ui\JsonHandler::encodeForHTML(false);
        }
        $data["messages"] = $this->getWarningMessages();

        $render = new \Anakeen\Ui\RenderDefault();

        $version = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");

        $data["ws"] = $version;
        $cssRefs = [
            "bootstrap" => \Anakeen\Ui\UIGetAssetPath::getCssBootstrap(),
            "kendo" => \Anakeen\Ui\UIGetAssetPath::getCssKendo(),
            "smartElement" => \Anakeen\Ui\UIGetAssetPath::getCssSmartElement()
        ];
        $cssRefs = array_merge($cssRefs, $render->getCssReferences());
        $css = array();
        foreach ($cssRefs as $key => $path) {
            $css[] = array(
                "key" => $key,
                "path" => $path
            );
        }
        $data["CSS"] = $css;
        $require = $render->getJsDeps();
        $js = array();
        foreach ($require as $key => $path) {
            $js[] = array(
                "key" => $key,
                "path" => $path,
                "noModule" => $key === "polyfill"
            );
        }
        $data["JS_DEPS"] = $js;

        $data["JS"] = [
            ["key" => "smartElement", "path" => $render->getSmartElementJs()]
        ];
        return $data;
    }

    protected function getWarningMessages()
    {
        $warnings = []; // @TODO record Warning Msg$action->parent->getWarningMsg();
        $messages = [];
        foreach ($warnings as $warning) {
            $message = new \Anakeen\Routes\Core\Lib\ApiMessage();
            $message->contentText = $warning;
            $message->type = $message::WARNING;

            $messages[] = $message;
        }
        //$action->parent->clearWarningMsg();
        return json_encode($messages);
    }
}
