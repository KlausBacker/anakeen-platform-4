<?php
/*
 * @author Anakeen
*/

namespace Dcp\Ui\Html;

use Dcp\HttpApi\V1\DocManager\DocManager as DocManager;
use Dcp\HttpApi\V1\Etag\Manager as EtagManager;
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
        
        $layout = new \Layout("DOCUMENT/Layout/view.html");
        $layout->set("BASEURL", self::getBaseUrl());
        $layout->set("NOTIFICATION_DELAY", \ApplicationParameterManager::getParameterValue("DOCUMENT", "NOTIFICATION_DELAY"));
        $layout->set("notificationLabelMore", ___("See more ...", "ddui:notification"));
        $layout->set("notificationTitleMore", ___("Notification", "ddui:notification"));
        
        if ($initid === false) {
            //Boot the init page in void mode (used by offline project)
            $etag = md5(sprintf("%s : %s", \ApplicationParameterManager::getParameterValue("CORE", "WVERSION") , \ApplicationParameterManager::getScopedParameterValue("CORE_LANG")));
            $etagManager = new EtagManager();
            if ($etagManager->verifyCache($etag)) {
                $etagManager->generateNotModifiedResponse($etag);
                $layout->template = "";
                $layout->noparse = true;
                header("Cache-Control:");
                return "";
            }
            $layout->set("viewInformation", \Dcp\Ui\JsonHandler::encodeForHTML(false));
            $etagManager->generateResponseHeader($etag);
        } else {
            $doc = DocManager::getDocument($initid);
            if (!$doc) {
                $e = new Exception(sprintf(___("Document identifier \"%s\"not found", "ddui") , $initid));
                $e->setHttpStatus("404", "Document not found");
                throw $e;
            }
            $err = $doc->control("view");
            if ($err) {
                $e = new Exception($err);
                $e->setHttpStatus("403", "Forbidden");
                throw $e;
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
            
            $layout->set("viewInformation", \Dcp\Ui\JsonHandler::encodeForHTML($viewInformation));
        }
        
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
    
    protected static function getBaseUrl()
    {
        $url = sprintf("%s://%s", self::getRequestScheme() , $_SERVER["SERVER_NAME"]);
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
    /**
     * Get the request's scheme in a "portable" way
     *
     * @return string
     */
    protected static function getRequestScheme()
    {
        if (!empty($_SERVER['REQUEST_SCHEME'])) {
            /*
             * $_SERVER['REQUEST_SCHEME'] is only available with Apache >= 2.4
             * and contains the scheme part of the request's URI
            */
            return $_SERVER['REQUEST_SCHEME'];
        } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            /*
             * "on" if the request uses https, "off" otherwise
            */
            return 'https';
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            /*
             * https://tools.ietf.org/html/rfc7239#section-5.4
             * The "proto" parameter has the value of the used protocol type.
            */
            return $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }
        return 'http';
    }
}
