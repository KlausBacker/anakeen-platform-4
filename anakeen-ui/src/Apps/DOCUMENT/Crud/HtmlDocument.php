<?php
/*
 * @author Anakeen
*/

namespace Dcp\Ui\Html;

use Dcp\HttpApi\V1\DocManager\DocManager as DocManager;
use Dcp\HttpApi\V1\Crud\Exception;

class Document extends \Dcp\HttpApi\V1\Crud\Crud
{
    protected $viewId = "!defaultConsultation";
    protected $revision = - 1;
    /**
     * Read a ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     */
    public function read($resourceId)
    {
        if ($resourceId === "0") {
            // Special case to load HTML page without documents
            $resourceId = false;
        }
        return $this->view($resourceId, $this->viewId, $this->revision);
    }
    /**
     * Set the revision and viewId for current request
     *
     * @param array $array
     * @throws Exception
     */
    public function setUrlParameters(array $array)
    {
        parent::setUrlParameters($array);
        
        if (isset($this->urlParameters["revision"])) {
            $this->revision = intval($this->urlParameters["revision"]);
        }
        if (!empty($this->urlParameters["viewIdentifier"])) {
            $this->viewId = ($this->urlParameters["viewIdentifier"]);
        }
    }
    /**
     * Create new ressource
     *
     * @return mixed
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     */
    public function create()
    {
        $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot create a html view");
        throw $exception;
    }
    /**
     * Update the ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     */
    public function update($resourceId)
    {
        $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot update a html view");
        throw $exception;
    }
    /**
     * Delete ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     */
    public function delete($resourceId)
    {
        $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot delete a html view");
        throw $exception;
    }
    /**
     * @param string|bool       $initid
     * @param string $viewId
     * @param int    $revision
     *
     * @return string
     * @throws Exception
     * @throws \Dcp\HttpApi\V1\DocManager\Exception
     */
    public function view($initid, $viewId = "!defaultConsultation", $revision = - 1)
    {
        if (!is_numeric($revision)) {
            if (!preg_match('/^state:(.+)$/', $revision, $regStates)) {
                throw new Exception(sprintf(___("Revision \"%s\" must be a number or a state reference", "ddui") , $revision));
            }
        }

        $modeDebug = \ApplicationParameterManager::getParameterValue("DOCUMENT", "MODE_DEBUG");
        if ($modeDebug !== "FALSE") {
            $layout = new \Layout("DOCUMENT/Layout/debug/view.html");
        } else {
            $layout = new \Layout("DOCUMENT/Layout/prod/view.html");
        }
        $layout->set("BASEURL", self::getBaseUrl());
        $layout->set("NOTIFICATION_DELAY", \ApplicationParameterManager::getParameterValue("DOCUMENT", "NOTIFICATION_DELAY"));
        $layout->set("notificationLabelMore", ___("See more ...", "ddui:notification"));
        $layout->set("notificationTitleMore", ___("Notification", "ddui:notification"));
        $layout->set("messages", "{}");
        if ($initid !== false) {
            $doc = DocManager::getDocument($initid);
            if (!$doc) {
                $e = new Exception(sprintf(___("Document identifier \"%s\"not found", "ddui") , $initid));
                $e->setHttpStatus("404", "Document not found");
                throw $e;
            }
            if ($viewId !== \Dcp\Ui\Crud\View::defaultViewCreationId && $viewId !== \Dcp\Ui\Crud\View::coreViewCreationId) {
                $err = $doc->control("view");
                if ($err) {
                    $e = new Exception(sprintf(___("Access not granted for document #%s", "ddui") , $initid));
                    $e->setHttpStatus("403", "Forbidden");
                    throw $e;
                }
            } else {
                $err = $doc->control("icreate");
                if ($err) {
                    $e = new Exception(sprintf(___("Access not granted to create \"%s\" document", "ddui") , $doc->getTitle()));
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
        
        $version = \ApplicationParameterManager::getParameterValue("CORE", "WVERSION");
        
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
            $message = new \Dcp\HttpApi\V1\Api\RecordReturnMessage();
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
            $url.= sprintf(":%s", $_SERVER["SERVER_PORT"]);
        }
        if (preg_match('@^(.*)/api/v1/@', $_SERVER["REQUEST_URI"], $reg)) {
            $url.= $reg[1];
        } else {
            if (preg_match('@^(.*)/\\?@', $_SERVER["REQUEST_URI"], $reg)) {
                $url.= $reg[1];
            }
        }
        $url.= "/";
        return $url;
    }
    
    public function getEtagInfo()
    {
        if (isset($this->urlParameters["identifier"])) {
            
            $id = $this->urlParameters["identifier"];
            $etag = sprintf("%s : %s : %s", \ApplicationParameterManager::getScopedParameterValue("WVERSION") , \ApplicationParameterManager::getScopedParameterValue("CORE_LANG"), $id);
            
            return $etag;
        }
        
        return "";
    }
}
