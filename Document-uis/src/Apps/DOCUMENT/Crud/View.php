<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Dcp\Ui\Crud;

use Dcp\AttributeIdentifiers\Cvdoc as CvdocAttribute;
use Dcp\HttpApi\V1\Crud\Crud;
use Dcp\HttpApi\V1\Crud\Document as DocumentCrud;
use Dcp\HttpApi\V1\Crud\DocumentUtils;
use Dcp\HttpApi\V1\Crud\Exception;
use Dcp\HttpApi\V1\DocManager\DocManager as DocManager;

class View extends Crud
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
    protected $revision = - 1;
    
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
     * @var \Doc current document
     */
    protected $document = null;
    protected $customClientData = null;
    protected $renderConfig = null;
    protected $renderVid = '';
    /**
     * Create new ressource
     * @throws Exception
     * @return mixed
     */
    public function create()
    {
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot create a view with the API");
        throw $exception;
    }
    /**
     * Read a resource
     * @param string|int $resourceId Resource identifier
     * @throws Exception
     * @return mixed
     */
    public function read($resourceId)
    {
        $refreshMsg = '';
        $creationMode = false;
        $family = null;
        if ($this->viewIdentifier === self::coreViewCreationId || $this->viewIdentifier === self::defaultViewCreationId) {
            /**
             * @var \DocFam $family
             */
            $family = DocManager::getFamily($resourceId);
            DocManager::cache()->addDocument($family);
            $this->createDocument($resourceId);
            $creationMode = true;
        } else {
            $this->getDocument($resourceId);
            $refreshMsg = $this->setRefresh();
        }
        
        if (!in_array($this->viewIdentifier, array(
            self::coreViewCreationId,
            self::defaultViewConsultationId,
            self::defaultViewEditionId,
            self::defaultViewCreationId,
            self::coreViewConsultationId,
            self::coreViewEditionId
        )) && !$this->document->cvid) {
            $exception = new Exception("CRUDUI0001", $this->viewIdentifier, $resourceId);
            $exception->setHttpStatus("404", "View not found");
            throw $exception;
        }
        
        $info = array(
            "uri" => $this->getUri($creationMode ? $family : $this->document, $this->viewIdentifier)
        );
        /**
         * @var \Dcp\Family\Cvdoc $controlView
         */
        $controlView = DocManager::getDocument($this->document->cvid);
        
        $vid = $this->viewIdentifier;
        
        $info["view"] = $this->getViewInformation($vid);
        
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
            $msg = new \Dcp\HttpApi\V1\Api\RecordReturnMessage();
            $msg->contentHtml = $refreshMsg;
            $msg->type = \Dcp\HttpApi\V1\Api\RecordReturnMessage::MESSAGE;
            $msg->code = "REFRESH";
            
            $this->addMessage($msg);
        }
        return $info;
    }
    /**
     * Update the ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     * @throws Exception
     * @throws \Dcp\Ui\Exception
     */
    public function update($resourceId)
    {
        $document = null;
        if ($this->viewIdentifier != self::coreViewEditionId) {
            $document = $this->getDocument($resourceId);
            // apply specified mask
            if (($this->viewIdentifier != self::defaultViewEditionId) && ($document->cvid > 0)) {
                // special controlled view
                
                /**
                 * @var \Dcp\Family\Cvdoc $cvdoc
                 */
                $cvdoc = DocManager::getDocument($document->cvid);
                $cvdoc->Set($document);
                $err = $cvdoc->control($this->viewIdentifier); // control special view
                if ($err != "") {
                    $exception = new Exception("CRUDUI0008", $this->viewIdentifier, $cvdoc->getTitle() , $document->getTitle());
                    $exception->setUserMessage($err);
                    $exception->setHttpStatus("403", "Access deny");
                    throw $exception;
                }
                $tview = $cvdoc->getView($this->viewIdentifier);
                $mask = $tview[CvdocAttribute::cv_mskid];
                if ($mask) {
                    $document->setMask($mask); // apply mask to avoid modification of invisible attribute
                    
                }
            } else if ($document->cvid > 0) {
                $document->setMask($document::USEMASKCVEDIT);
            }
        }
        
        if ($document && !$document->isAlive() && isset($this->properties["status"]) && $this->properties["status"] === "alive") {
            //Handle restoration
            $err = $document->undelete();
            if ($err) {
                throw new \Dcp\Ui\Exception("Unable to restore $err");
            }
        } else {
            $documentData = new DocumentCrud();
            $documentData->setContentParameters($this->contentParameters);
            $documentData->update($resourceId);
        }
        
        $this->document = null;
        return $this->read($resourceId);
    }
    /**
     * Delete ressource
     * @param string|int $resourceId Resource identifier
     * @throws Exception
     * @return mixed
     */
    public function delete($resourceId)
    {
        $documentData = new DocumentCrud();
        $documentData->delete($resourceId);
        $this->document = null;
        return $this->read($resourceId);
    }
    /**
     * Compute abstract standard view
     *
     * @param \Doc $document
     * @return array
     */
    protected function getCoreViews(\Doc $document)
    {
        $defaultConsultation = array(
            "requestIdentifier" => $this->viewIdentifier,
            "uri" => $this->getUri($document, self::coreViewConsultationId) ,
            "identifier" => self::coreViewConsultationId,
            "mode" => "consultation",
            "label" => ___("Core View Consultation", "ddui") ,
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
            "uri" => $this->getUri($document, self::coreViewEditionId) ,
            "identifier" => self::coreViewEditionId,
            "mode" => "edition",
            "label" => ___("Core View Edition", "ddui") ,
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
            "uri" => $this->getUri($document, self::coreViewCreationId) ,
            "identifier" => self::coreViewCreationId,
            "mode" => "edition",
            "creationView" => true,
            "label" => ___("Core View Create", "ddui") ,
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
     * @param \Dcp\Family\Cvdoc $controlView
     * @param array $viewInfo
     * @return array
     */
    protected function getViewProperties(\Dcp\Family\Cvdoc $controlView, array $viewInfo)
    {
        $viewId = $viewInfo[CvdocAttribute::cv_idview];
        return array(
            "requestIdentifier" => $this->viewIdentifier,
            "identifier" => $viewId,
            "mode" => ($viewInfo[CvdocAttribute::cv_kview] === "VCONS") ? "consultation" : "edition",
            "label" => $controlView->getLocaleViewLabel($viewId) ,
            "isDisplayable" => ($viewInfo[CvdocAttribute::cv_displayed] === "yes") ,
            "order" => intval($viewInfo[CvdocAttribute::cv_order]) ,
            self::fieldMenu => $controlView->getLocaleViewMenu($viewId) ,
            "mask" => array(
                "id" => intval($viewInfo[CvdocAttribute::cv_mskid]) ,
                "title" => $viewInfo[CvdocAttribute::cv_msk]
            )
        );
    }
    /**
     * @param string $viewId view identifier
     * @return array
     * @throws Exception
     */
    protected function getViewInformation(&$viewId)
    {
        $config = $this->getRenderConfig($viewId);
        $fields = $this->getFields();
        
        \Dcp\ConsoleTime::startPartial("View Info");
        $viewInfo = array();
        foreach ($fields as $field) {
            switch ($field) {
                case self::fieldRenderOptions:
                    $configOptions = $config->getOptions($this->document);
                    if (!is_a($configOptions, "Dcp\\Ui\\RenderOptions")) {
                        throw new \Dcp\Ui\Exception("UI0013", get_class($config));
                    }
                    $viewInfo[self::fieldRenderOptions] = $configOptions->jsonSerialize();
                    $viewInfo[self::fieldRenderOptions]["visibilities"] = $config->getVisibilities($this->document)->jsonSerialize();
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
            \Dcp\ConsoleTime::step($field);
        }
        \Dcp\ConsoleTime::stopPartial();
        return $viewInfo;
    }
    /**
     * @param \Dcp\Ui\IRenderConfig $config
     * @param \Doc $document
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
     * @param \Dcp\Ui\IRenderConfig $config
     * @param \Doc $document
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
     * Get the current local
     *
     * @return array|bool
     */
    protected function getLocaleData()
    {
        $localeId = \ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
        $config = getLocaleConfig($localeId);
        return $config;
    }
    /**
     * Get the document from the standard CRUD
     *
     * @param \Doc $document
     * @return mixed
     */
    protected function renderDocument($document)
    {
        $documentData = new DocumentCrud();
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
        $documentData->setDefaultFields(implode(",", $fields));
        return $documentData->getInternal($document);
    }
    /**
     * Get the current document,
     * record to document protected attribute
     * @param $resourceId
     * @return \Doc
     * @throws Exception
     * @throws \Dcp\HttpApi\V1\DocManager\Exception
     */
    protected function getDocument($resourceId)
    {
        if ($this->document === null) {
            // Do not twice
            if ($this->revision === - 1) {
                $this->document = DocManager::getDocument($resourceId);
            } else {
                $revId = DocManager::getRevisedDocumentId($resourceId, $this->revision);
                $this->document = DocManager::getDocument($revId, false);
            }
            if ($this->document === null) {
                $exception = new Exception("CRUD0200", $resourceId);
                $exception->setHttpStatus("404", "Document not found");
                throw $exception;
            }
            
            DocManager::cache()->addDocument($this->document);
        }
        return $this->document;
    }
    
    protected function createDocument($resourceId)
    {
        $this->document = DocManager::createDocument($resourceId);
        
        if ($this->document === null) {
            $exception = new Exception("CRUD0200", $resourceId);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }
        $this->document->title = sprintf(___("%s Creation", "ddui") , $this->document->getFamilyDocument()->getTitle());
        return $this->document;
    }
    /**
     * @param \Dcp\Ui\IRenderConfig $config
     * @param \Doc                  $document
     *
     * @return string
     * @throws \Dcp\Ui\Exception
     */
    protected function renderTemplates(\Dcp\Ui\IRenderConfig $config, \Doc $document)
    {
        $templates = $config->getTemplates($document);
        
        if (!is_array($templates)) {
            throw new \Dcp\Ui\Exception("UI0012", get_class($config));
        }
        $delimiterStartTag = '[[';
        $delimiterEndTag = ']]';
        $option = array(
            'cache' => DEFAULT_PUBDIR . '/var/cache/mustache',
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
    
    protected function getUri(\Doc $document, $vid)
    {
        if ($this->revision === - 1) {
            return $this->generateURL(sprintf("documents/%s/views/%s", $document->initid, $vid));
        } else {
            return $this->generateURL(sprintf("documents/%s/revisions/%d/views/%s", $document->initid, $this->revision, $vid));
        }
    }
    /**
     * Get the restrict fields value
     *
     * The restrict fields is used for restrict the return of the get request
     *
     * @throws Exception
     * @return array
     */
    protected function getFields()
    {
        if (!empty($this->contentParameters["fields"])) {
            $parameterfields = $this->contentParameters["fields"];
            $fields = array_map("trim", explode(",", $parameterfields));
            foreach ($fields as $field) {
                if (!in_array($field, $this->fields)) {
                    throw new Exception("CRUDUI0004", $field, implode(", ", $this->fields));
                }
            }
        } else {
            $fields = $this->fields;
        }
        if (!empty($this->contentParameters["noStructureFamily"])) {
            $this->needSendFamilyStructure = false;
        }
        
        return $fields;
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
                    if ($this->document->locked == - 1) {
                        throw new Exception("CRUDUI0005", $vid);
                    }
                    if ($renderMode === "create") {
                        
                        $err = $this->document->control("icreate");
                        $err.= $this->document->control("create");
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
            $this->renderConfig = $config;
            $this->renderVid = $vid;
        }
        $vid = $this->renderVid;
        return $this->renderConfig;
    }
    /**
     * Set the url parameters
     *
     * @param array $parameters
     */
    public function setUrlParameters(array $parameters)
    {
        parent::setUrlParameters($parameters);
        if (isset($this->urlParameters["identifier"])) {
            $this->resourceIdentifier = $this->urlParameters["identifier"];
        }
        if (isset($this->urlParameters["viewIdentifier"])) {
            $this->viewIdentifier = $this->urlParameters["viewIdentifier"];
        }
        if (isset($this->urlParameters["revision"])) {
            $this->revision = $this->urlParameters["revision"];
        }
    }
    /**
     * Return etag info
     *
     * @return null|string
     */
    public function getEtagInfo()
    {
        if (isset($this->urlParameters["identifier"])) {
            $id = $this->urlParameters["identifier"];
            $id = DocManager::getIdentifier($id, true);
            return $this->extractEtagDocument($id);
        }
        return null;
    }
    /**
     * Compute etag from an id
     *
     * @param $id
     *
     * @return string
     * @throws \Dcp\Db\Exception
     */
    protected function extractEtagDocument($id)
    {
        
        \Dcp\ConsoleTime::startPartial("Etag");
        \Dcp\ConsoleTime::step("etag");
        $this->getDocument($id);
        \Dcp\ConsoleTime::step("getDocument");
        
        $refreshMsg = $this->setRefresh();
        \Dcp\ConsoleTime::step("refresh");
        
        $disableEtag = \Dcp\Ui\RenderConfigManager::getRenderParameter($this->document->fromname, "disableEtag");
        \Dcp\ConsoleTime::step("eTag test");
        
        if ($disableEtag) {
            \Dcp\ConsoleTime::stopPartial();
            return null;
        }
        $viewId = $this->viewIdentifier;
        if ($viewId !== self::coreViewCreationId && $this->document->doctype !== "C") {
            $config = $this->getRenderConfig($viewId);
            $renderEtag = $config->getEtag($this->document);
            if ($renderEtag !== "") {
                \Dcp\ConsoleTime::stopPartial();
                return $renderEtag . $refreshMsg;
            }
        }
        
        $etag = $this->getDefaultETag($this->document);
        
        $etag.= $refreshMsg;
        \Dcp\ConsoleTime::stopPartial();
        
        return $etag;
    }
    
    public static function getDefaultETag(\Doc $document)
    {
        $result = array(
            "id" => $document->id,
            "revdate" => $document->revdate,
            "cvid" => $document->cvid,
            "views" => $document->views,
            "fromid" => $document->fromid,
            "locked" => $document->locked,
        );
        
        $user = getCurrentUser();
        $result[] = $user->id;
        $result[] = $user->memberof;
        
        $sql = sprintf("select revdate from docfam where id = %d", $result["fromid"]);
        simpleQuery(getDbAccess() , $sql, $familyRevdate, true, true);
        $result[] = $familyRevdate;
        
        $sql = sprintf("select comment from docutag where tag='lasttab' and id = %d", $document->id);
        simpleQuery(getDbAccess() , $sql, $lastTab, true, true);
        $result[] = $lastTab;
        
        if ($result["cvid"]) {
            $sql = sprintf("select revdate from docread where id = %d", $result["cvid"]);
            simpleQuery(getDbAccess() , $sql, $cvDate, true, true);
            $result[] = $cvDate;
        }
        // Necessary only when use family.structure
        $result[] = \ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
        $result[] = \ApplicationParameterManager::getScopedParameterValue("WVERSION");
        
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
        if (isset($this->contentParameters[self::fieldCustomClientData])) {
            $this->customClientData = json_decode($this->contentParameters[self::fieldCustomClientData], true);
            return $this->customClientData;
        }
        return null;
    }
    
    public function analyseJSON($jsonString)
    {
        $dataDocument = json_decode($jsonString, true);
        $this->customClientData = isset($dataDocument[self::fieldCustomClientData]) ? $dataDocument[self::fieldCustomClientData] : null;
        $values = DocumentUtils::analyzeDocumentJSON($jsonString);
        if (isset($dataDocument["document"]["properties"])) {
            $this->properties = $dataDocument["document"]["properties"];
        }
        return $values;
    }
}
