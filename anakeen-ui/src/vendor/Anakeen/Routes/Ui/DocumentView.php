<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Router\URLUtils;
use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\SmartElementManager;
use Dcp\Ui\RenderOptions;
use SmartStructure\Fields\Cvdoc as CvdocAttribute;
use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\Settings;
use Anakeen\Router\Exception;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class DocumentView
 *
 * @note    Used by route : GET /api/v2/documents/{docid}/revisions/{revision}/views/{view}
 * @note    Used by route : GET /api/v2/documents/{docid}/views/{view}
 * @package Anakeen\Routes\Ui
 */
class DocumentView
{
    const defaultViewConsultationId = "!defaultConsultation";
    const defaultViewEditionId = "!defaultEdition";
    const defaultViewCreationId = "!defaultCreation";
    const coreViewConsultationId = "!coreConsultation";
    const coreViewEditionId = "!coreEdition";
    const coreViewCreationId = "!coreCreation";
    const fieldTemplate = "templates";
    const fieldRenderOptions = "renderOptions";
    const fieldCustomServerData = "customServerData";
    const fieldCustomClientData = "customClientData";
    const fieldDocumentData = "documentData";
    const fieldLocale = "locale";
    const fieldStyle = "style";
    const fieldMenu = "menu";
    const fieldScript = "script";
    const fieldRenderLabel = "renderLabel";
    /**
     * @var string view Identifier must match one of view control associated document
     */
    protected $viewIdentifier = '';
    protected $properties = array();

    protected $resourceIdentifier = '';
    /**
     * @var int revision number - -1 means latest
     */
    protected $revision = -1;

    protected $fields = array(
        self::fieldCustomClientData,
        self::fieldRenderOptions,
        self::fieldRenderLabel,
        self::fieldCustomServerData,
        self::fieldMenu,
        self::fieldTemplate,
        self::fieldDocumentData,
        self::fieldLocale,
        self::fieldStyle,
        self::fieldScript
    );
    protected $needSendFamilyStructure = false;
    /**
     * @var \Anakeen\Core\Internal\SmartElement current document
     */
    protected $document = null;
    protected $customClientData = null;
    protected $renderConfig = null;
    protected $renderVid = '';

    protected $documentId;
    protected $requestFields = [];

    /**
     * Read a resource
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     * @return mixed
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);


        $etag = $this->getEtagInfo($this->documentId);
        if ($etag) {
            $response = ApiV2Response::withEtag($request, $response, $etag);
            if (ApiV2Response::matchEtag($request, $etag)) {
                return $response;
            }
        }

        if (!in_array($this->viewIdentifier, array(
                self::coreViewCreationId,
                self::defaultViewConsultationId,
                self::defaultViewEditionId,
                self::defaultViewCreationId,
                self::coreViewConsultationId,
                self::coreViewEditionId
            )) && !$this->document->cvid) {
            $exception = new Exception("CRUDUI0001", $this->viewIdentifier, $this->documentId);
            $exception->setHttpStatus("404", "View not found");
            throw $exception;
        }

        $info = $this->doRequest($messages);
        return ApiV2Response::withData($response, $info, $messages);
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->documentId = $args["docid"];
        $this->viewIdentifier = $args["view"];
        if (isset($args["revision"])) {
            $this->revision = $args["revision"];
        }

        if (!empty($request->getQueryParam("fields"))) {
            $parameterfields = $request->getQueryParam("fields");
            $this->requestFields = array_map("trim", explode(",", $parameterfields));
            foreach ($this->requestFields as $field) {
                if (!in_array($field, $this->fields)) {
                    throw new Exception("CRUDUI0004", $field, implode(", ", $this->fields));
                }
            }
        } else {
            $this->requestFields = $this->fields;
        }

        if (!empty($request->getQueryParam("noStructureFamily"))) {
            $this->needSendFamilyStructure = false;
        }

        //Add custom client data
        if ($request->getMethod() === "GET" && !empty($request->getQueryParam(self::fieldCustomClientData))) {
            $this->customClientData = json_decode($request->getQueryParam(self::fieldCustomClientData), true);
        }

        if (in_array($request->getMethod(), ["POST", "PUT"])) {
            $body = $request->getParsedBody();
            if (isset($body[self::fieldCustomClientData])) {
                $this->customClientData = $body[self::fieldCustomClientData];
            }
        }
    }

    protected function doRequest(&$messages = [])
    {
        $refreshMsg = '';
        $creationMode = false;
        $family = null;
        if ($this->viewIdentifier === self::coreViewCreationId || $this->viewIdentifier === self::defaultViewCreationId) {
            /**
             * @var \Anakeen\Core\SmartStructure $family
             */
            $family = SEManager::getFamily($this->documentId);
            $this->createDocument($this->documentId);
            $creationMode = true;
        } else {
            $this->getDocument($this->documentId);
            $refreshMsg = $this->setRefresh();
        }

        $info = array(
            "uri" => $this->getUri($creationMode ? $family : $this->document, $this->viewIdentifier)
        );
        /**
         * @var \SmartStructure\Cvdoc $controlView
         */
        $controlView = SEManager::getDocument($this->document->cvid);
        if ($controlView) {
            SEManager::cache()->addDocument($controlView);
        }

        $vid = $this->viewIdentifier;
        $messages = [];
        $info["view"] = $this->getViewInformation($vid, $messages);

        if ($vid === "") {
            $coreViews = $this->getCoreViews($this->document);
            if ($this->viewIdentifier === self::defaultViewConsultationId) {
                $info["properties"] = $coreViews[self::coreViewConsultationId];
            } elseif ($this->viewIdentifier === self::defaultViewEditionId) {
                $info["properties"] = $coreViews[self::coreViewEditionId];
            } elseif ($this->viewIdentifier === self::coreViewConsultationId) {
                $info["properties"] = $coreViews[self::coreViewConsultationId];
            } elseif ($this->viewIdentifier === self::coreViewEditionId) {
                $info["properties"] = $coreViews[self::coreViewEditionId];
            } elseif ($this->viewIdentifier === self::coreViewCreationId) {
                $info["properties"] = $coreViews[self::coreViewCreationId];
            } elseif ($this->viewIdentifier === self::defaultViewCreationId) {
                $info["properties"] = $coreViews[self::coreViewCreationId];
            }
        } else {
            $viewInfo = $controlView->getView($vid);
            $info["properties"] = $this->getViewProperties($controlView, $viewInfo);
            if ($creationMode) {
                $info["properties"]["creationView"] = true;
            }
        }

        if ($refreshMsg) {
            $msg = new \Anakeen\Routes\Core\Lib\ApiMessage();
            $msg->contentHtml = $refreshMsg;
            $msg->type = \Anakeen\Routes\Core\Lib\ApiMessage::MESSAGE;
            $msg->code = "REFRESH";
            $messages[] = $msg;
        }
        return $info;
    }

    /**
     * Compute abstract standard view
     *
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return array
     */
    protected function getCoreViews(\Anakeen\Core\Internal\SmartElement $document)
    {
        $defaultConsultation = array(
            "requestIdentifier" => $this->viewIdentifier,
            "uri" => $this->getUri($document, self::coreViewConsultationId),
            "identifier" => self::coreViewConsultationId,
            "mode" => "consultation",
            "label" => ___("Core View Consultation", "ddui"),
            "isDisplayable" => false,
            "order" => 0,
            self::fieldMenu => "",
            "mask" => array(
                "id" => 0,
                "title" => ""
            )
        );
        $defaultEdition = array(
            "requestIdentifier" => $this->viewIdentifier,
            "uri" => $this->getUri($document, self::coreViewEditionId),
            "identifier" => self::coreViewEditionId,
            "mode" => "edition",
            "label" => ___("Core View Edition", "ddui"),
            "isDisplayable" => false,
            "order" => 0,
            self::fieldMenu => "",
            "mask" => array(
                "id" => 0,
                "title" => ""
            )
        );

        $defaultCreate = array(
            "requestIdentifier" => $this->viewIdentifier,
            "uri" => $this->getUri($document, self::coreViewCreationId),
            "identifier" => self::coreViewCreationId,
            "mode" => "edition",
            "creationView" => true,
            "label" => ___("Core View Create", "ddui"),
            "isDisplayable" => false,
            "order" => 0,
            self::fieldMenu => "",
            "mask" => array(
                "id" => 0,
                "title" => ""
            )
        );

        return array(
            self::coreViewConsultationId => $defaultConsultation,
            self::coreViewEditionId => $defaultEdition,
            self::coreViewCreationId => $defaultCreate
        );
    }

    /**
     * Compute properties
     *
     * @param \SmartStructure\Cvdoc $controlView
     * @param array                 $viewInfo
     * @return array
     */
    protected function getViewProperties(\SmartStructure\Cvdoc $controlView, array $viewInfo)
    {
        $viewId = $viewInfo[CvdocAttribute::cv_idview];
        return array(
            "requestIdentifier" => $this->viewIdentifier,
            "identifier" => $viewId,
            "mode" => ($viewInfo[CvdocAttribute::cv_kview] === "VCONS") ? "consultation" : "edition",
            "label" => $controlView->getLocaleViewLabel($viewId),
            "isDisplayable" => ($viewInfo[CvdocAttribute::cv_displayed] === "yes"),
            "order" => intval($viewInfo[CvdocAttribute::cv_order]),
            self::fieldMenu => $controlView->getLocaleViewMenu($viewId),
            "mask" => array(
                "id" => intval($viewInfo[CvdocAttribute::cv_mskid]),
                "title" => \DocTitle::getTitle($viewInfo[CvdocAttribute::cv_mskid])
            )
        );
    }

    /**
     * @param string       $viewId view identifier
     * @param ApiMessage[] $messages
     * @return array
     * @throws Exception
     * @throws \Dcp\Ui\Exception
     */
    protected function getViewInformation(&$viewId, &$messages)
    {
        $config = $this->getRenderConfig($viewId);
        $fields = $this->getFields();

        $viewInfo = array();
        foreach ($fields as $field) {
            switch ($field) {
                case self::fieldRenderOptions:
                    $configOptions = $config->getOptions($this->document);
                    if (!is_a($configOptions, RenderOptions::class)) {
                        throw new \Dcp\Ui\Exception("UI0013", get_class($config));
                    }

                    $viewInfo[self::fieldRenderOptions] = $configOptions->jsonSerialize();
                    $viewInfo[self::fieldRenderOptions]["visibilities"] = $config->getVisibilities($this->document, $this->getMask($viewId))->jsonSerialize();
                    $viewInfo[self::fieldRenderOptions]["needed"] = $config->getNeeded($this->document)->jsonSerialize();

                    break;

                case self::fieldRenderLabel:
                    $viewInfo[self::fieldRenderLabel] = $config->getLabel();
                    break;

                case self::fieldMenu:
                    $viewInfo[self::fieldMenu] = $config->getMenu($this->document);
                    break;

                case self::fieldTemplate:
                    $viewInfo[self::fieldTemplate] = $this->renderTemplates($config, $this->document);
                    break;

                case self::fieldDocumentData:
                    $viewInfo[self::fieldDocumentData] = $this->renderDocument($this->document);
                    break;

                case self::fieldLocale:
                    $viewInfo[self::fieldLocale] = $this->getLocaleData();
                    break;

                case self::fieldStyle:
                    $viewInfo[self::fieldStyle] = $this->getStyleData($config, $this->document);
                    break;

                case self::fieldScript:
                    $viewInfo[self::fieldScript] = $this->getScriptData($config, $this->document);
                    break;

                case self::fieldCustomServerData:
                    $viewInfo[self::fieldCustomServerData] = $config->getCustomServerData($this->document);
                    break;

                case self::fieldCustomClientData:
                    $config->setCustomClientData($this->document, $this->getCustomClientData());
                    break;
            }

            $messages = $this->getMessages($config, $this->document);
        }
        return $viewInfo;
    }

    protected function getMask($viewId)
    {
        if (!$viewId || $viewId[0] === "!" || !$this->document->cvid) {
            return null;
        }
        /**
         * @var \SmartStructure\Cvdoc $cvDoc
         */
        $cvDoc = SEManager::getDocument($this->document->cvid);
        $vInfo = $cvDoc->getView($viewId);

        if (!empty($vInfo[\SmartStructure\Fields\Cvdoc::cv_mskid])) {
            $mskId = $vInfo[\SmartStructure\Fields\Cvdoc::cv_mskid];
            $msk =  SEManager::getDocument($mskId);
            SEManager::cache()->addDocument($msk);
            return $msk;
        }
        return null;
    }

    /**
     * @param \Dcp\Ui\IRenderConfig               $config
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return array|bool
     */
    protected function getStyleData($config, $document)
    {
        $cssList = $config->getCssReferences($document);
        $cssArray = array();
        foreach ($cssList as $cssId => $cssPath) {
            $cssArray[] = array(
                "path" => $cssPath,
                "key" => $cssId
            );
        }

        return array(
            "css" => $cssArray
        );
    }

    /**
     * @param \Dcp\Ui\IRenderConfig               $config
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @throws Exception
     * @return array|bool
     */
    protected function getScriptData($config, $document)
    {
        $jsList = $config->getJsReferences($document);
        if (!is_array($jsList)) {
            throw new Exception("CRUDUI0007");
        }
        $jsArray = array();
        foreach ($jsList as $cssId => $cssPath) {
            $jsArray[] = array(
                "path" => $cssPath,
                "key" => $cssId
            );
        }

        return array(
            "js" => $jsArray
        );
    }


    /**
     * @param \Dcp\Ui\IRenderConfig               $config
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return ApiMessage[]
     */
    protected function getMessages($config, $document)
    {
        $messages = $config->getMessages($document);
        return $messages;
    }

    /**
     * Get the current local
     *
     * @return array|bool
     */
    protected function getLocaleData()
    {
        $localeId = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_LANG");
        $config = ContextManager::getLocaleConfig($localeId);
        return $config;
    }

    /**
     * Get the document from the standard CRUD
     *
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return mixed
     */
    protected function renderDocument($document)
    {
        $documentData = new \Anakeen\Routes\Core\Lib\DocumentApiData($document);
        $fields = array(
            "document.attributes",
            "document.properties.family",
            "document.properties.icon",
            "document.properties.status",
            "document.properties.revision",
            "document.properties.security",
            "document.properties.state",
            "document.properties.type"
        );
        if ($this->needSendFamilyStructure && $document->doctype !== "C") {
            $fields[] = "family.structure";
        }
        $documentData->setFields($fields);
        return $documentData->getDocumentData();
    }

    /**
     * Get the current document,
     * record to document protected attribute
     * @param $resourceId
     * @return \Anakeen\Core\Internal\SmartElement
     * @throws Exception
     */
    protected function getDocument($resourceId)
    {
        if ($this->document === null) {
            // Do not twice
            if ($this->revision === -1) {
                $this->document = SmartElementManager::getDocument($resourceId);
            } else {
                $revId = SEManager::getRevisedDocumentId($resourceId, $this->revision);
                $this->document = SmartElementManager::getDocument($revId, false);
            }
            if ($this->document === null) {
                $exception = new Exception("CRUD0200", $resourceId);
                $exception->setHttpStatus("404", "Document not found");
                throw $exception;
            }

            SEManager::cache()->addDocument($this->document);
        }
        return $this->document;
    }

    protected function createDocument($resourceId)
    {
        $this->document = SmartElementManager::createDocument($resourceId, false);

        if ($this->document === null) {
            $exception = new Exception("CRUD0200", $resourceId);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }

        $family = $this->document->getFamilyDocument();
        // No use default values for column attribute, the ui use structure data to add column default values
        $attrs = $this->document->getNormalAttributes();
        foreach ($attrs as $aid => $attr) {
            if ($attr->type === "array") {
                $attr->setOption("empty", "yes");
            }
        }

        $this->document->setDefaultValues($family->getDefValues());

        $this->document->title = sprintf(
            ___("%s Creation", "ddui"),
            $this->document->getFamilyDocument()->getTitle()
        );
        return $this->document;
    }

    /**
     * @param \Dcp\Ui\IRenderConfig               $config
     * @param \Anakeen\Core\Internal\SmartElement $document
     *
     * @return string
     * @throws \Dcp\Ui\Exception
     */
    protected function renderTemplates(\Dcp\Ui\IRenderConfig $config, \Anakeen\Core\Internal\SmartElement $document)
    {
        $templates = $config->getTemplates($document);

        if (!is_array($templates)) {
            throw new \Dcp\Ui\Exception("UI0012", get_class($config));
        }
        $delimiterStartTag = '[[';
        $delimiterEndTag = ']]';
        $option = array(
            'cache' => DEFAULT_PUBDIR . '/' . Settings::CacheDir . 'mustache',
            'cache_file_mode' => 0600,
            'cache_lambda_templates' => true
        );
        $mustacheEngine = new \Mustache_Engine($option);

        $uiMustacheLoader = new \Dcp\Ui\MustacheLoaderSection($templates, $delimiterStartTag, $delimiterEndTag);
        $uiMustacheLoader->setDocument($document);
        $mustacheEngine->setPartialsLoader($uiMustacheLoader);
        $delimiter = sprintf('{{=%s %s=}}', $delimiterStartTag, $delimiterEndTag);
        $docController = $config->getContextController($document);

        $mainTemplate = "[[>templates]]";
        return json_decode($mustacheEngine->render($delimiter . $mainTemplate, $docController));
    }

    protected function getUri(\Anakeen\Core\Internal\SmartElement $document, $vid)
    {
        if ($this->revision === -1) {
            return URLUtils::generateURL(sprintf("%s/documents/%s/views/%s", Settings::ApiV2, $document->initid, $vid));
        } else {
            return URLUtils::generateURL(sprintf("%s/documents/%s/revisions/%d/views/%s", Settings::ApiV2, $document->initid, $this->revision, $vid));
        }
    }


    /**
     * Get the restrict fields value
     *
     * The restrict fields is used for restrict the return of the get request
     *
     * @return array
     */
    protected function getFields()
    {
        return $this->requestFields;
    }

    /**
     * @param string $vid
     * @return \Dcp\Ui\IRenderConfig
     * @throws Exception
     * @throws \Dcp\Ui\Exception
     */
    protected function getRenderConfig(&$vid)
    {
        if ($this->renderConfig === null) {
            $renderMode = "view";
            if ($vid == self::defaultViewConsultationId) {
                $renderMode = \Dcp\Ui\RenderConfigManager::ViewMode;
                $vid = '';
            } elseif ($vid == self::defaultViewEditionId) {
                $renderMode = \Dcp\Ui\RenderConfigManager::EditMode;
                $vid = '';
            } elseif ($vid == self::defaultViewCreationId) {
                $renderMode = \Dcp\Ui\RenderConfigManager::CreateMode;
                $vid = '';
            } elseif ($vid == self::coreViewConsultationId) {
                $renderMode = \Dcp\Ui\RenderConfigManager::ViewMode;
                $vid = '!none';
            } elseif ($vid == self::coreViewEditionId) {
                $renderMode = \Dcp\Ui\RenderConfigManager::EditMode;
                $vid = '!none';
            } elseif ($vid == self::coreViewCreationId) {
                $renderMode = \Dcp\Ui\RenderConfigManager::CreateMode;
                $vid = '!none';
            }
            if (($vid === "!none" || $this->document->cvid == 0) && $this->document->doctype !== "C") {
                $config = \Dcp\Ui\RenderConfigManager::getDefaultFamilyRenderConfig($renderMode, $this->document);
                $vid = '';
            } else {
                $config = \Dcp\Ui\RenderConfigManager::getRenderConfig($renderMode, $this->document, $vid);
            }
            switch ($config->getType()) {
                case "view":
                    $err = $this->document->control("view");
                    if ($err) {
                        $exception = new Exception("CRUD0201", $this->resourceIdentifier, $err);
                        $exception->setHttpStatus("403", "Forbidden");
                        throw $exception;
                    }
                    break;

                case "edit":
                    if ($this->document->locked == -1) {
                        throw new Exception("CRUDUI0005", $vid);
                    }
                    if ($renderMode === "create") {
                        $err = $this->document->control("icreate");
                        $err .= $this->document->control("create");
                        if ($err) {
                            $exception = new Exception("CRUD0201", $this->resourceIdentifier, $err);
                            $exception->setHttpStatus("403", "Forbidden");
                            throw $exception;
                        }
                    } else {
                        $err = $this->document->canEdit();
                        if ($err) {
                            $exception = new Exception("CRUD0201", $this->resourceIdentifier, $err);
                            $exception->setHttpStatus("403", "Forbidden");
                            throw $exception;
                        }
                    }
            }
            if ($this->document->isConfidential()) {
                $err = "Confidential document";
                $exception = new Exception("CRUD0201", $this->resourceIdentifier, $err);
                $exception->setHttpStatus("403", "Forbidden");
                throw $exception;
            }
            $this->renderConfig = $config;
            $this->renderVid = $vid;
        }
        $vid = $this->renderVid;
        return $this->renderConfig;
    }

    /**
     * Return etag info
     *
     * @param string $docid
     * @return null|string
     */
    protected function getEtagInfo($docid)
    {
        SEManager::getIdentifier($docid, true);
        return $this->extractEtagDocument($docid);
    }

    /**
     * Compute etag from an id
     *
     * @param $id
     *
     * @return string
     */
    protected function extractEtagDocument($id)
    {

        $this->getDocument($id);

        $refreshMsg = $this->setRefresh();

        $disableEtag = \Dcp\Ui\RenderConfigManager::getRenderParameter($this->document->fromname, "disableEtag");

        if ($disableEtag) {
            return null;
        }
        $viewId = $this->viewIdentifier;
        if ($viewId !== self::coreViewCreationId && $this->document->doctype !== "C") {
            $config = $this->getRenderConfig($viewId);
            $renderEtag = $config->getEtag($this->document);
            if ($renderEtag !== "") {
                return $renderEtag . $refreshMsg;
            }
        }

        $etag = $this->getDefaultETag($this->document);

        $etag .= $refreshMsg;

        return $etag;
    }

    public static function getDefaultETag(\Anakeen\Core\Internal\SmartElement $document)
    {
        $result = array(
            "id" => $document->id,
            "revdate" => $document->revdate,
            "cvid" => $document->cvid,
            "views" => $document->views,
            "fromid" => $document->fromid,
            "locked" => $document->locked,
        );

        $user = ContextManager::getCurrentUser();
        $result[] = $user->id;
        $result[] = $user->memberof;

        $sql = sprintf("select revdate from docfam where id = %d", $result["fromid"]);

        DbManager::query($sql, $familyRevdate, true, true);
        $result[] = $familyRevdate;

        $sql = sprintf("select comment from docutag where tag='lasttab' and id = %d", $document->id);
        DbManager::query($sql, $lastTab, true, true);
        $result[] = $lastTab;

        if ($result["cvid"]) {
            $sql = sprintf("select revdate from docread where id = %d", $result["cvid"]);
            DbManager::query($sql, $cvDate, true, true);
            $result[] = $cvDate;
        }
        // Necessary only when use family.structure
        $result[] = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_LANG");
        $result[] = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");

        return join(" ", $result);
    }

    /**
     * Apply refresh if application manager indicate applyRefresh for family
     * @return string refresh message
     */
    protected function setRefresh()
    {
        static $onlyOne = false;
        static $refreshMsg = '';

        if (!$onlyOne) {
            $applyRefresh = \Dcp\Ui\RenderConfigManager::getRenderParameter($this->document->fromname, "applyRefresh");
            if ($applyRefresh) {
                $refreshMsg = $this->document->refresh();
            }
            $onlyOne = true;
        }
        return $refreshMsg;
    }

    protected function getCustomClientData()
    {
        if ($this->customClientData) {
            return $this->customClientData;
        }

        return null;
    }
}
