<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
namespace Dcp\Ui\Crud;

use Dcp\AttributeIdentifiers\Cvrender;
use Dcp\HttpApi\V1\Crud\Crud;
use Dcp\HttpApi\V1\Crud\Document as DocumentCrud;
use Dcp\HttpApi\V1\Crud\DocumentUtils;
use Dcp\HttpApi\V1\Crud\Exception;
use Dcp\HttpApi\V1\Crud\Family;
use Dcp\HttpApi\V1\Crud\FamilyDocument;
use Dcp\HttpApi\V1\DocManager\DocManager as DocManager;

class View extends Crud
{
    
    const defaultViewConsultationId = "!defaultConsultation";
    const defaultViewEditionId = "!defaultEdition";
    const coreViewConsultationId = "!coreConsultation";
    const coreViewEditionId = "!coreEdition";
    const coreViewCreationId = "!coreCreation";
    const fieldTemplate = "templates";
    const fieldRenderOptions = "renderOptions";
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
    
    protected $resourceIdentifier = '';
    /**
     * @var int revision number - -1 means latest
     */
    protected $revision = - 1;
    
    protected $fields = array(
        self::fieldRenderLabel,
        self::fieldMenu,
        self::fieldTemplate,
        self::fieldRenderOptions,
        self::fieldDocumentData,
        self::fieldLocale,
        self::fieldStyle,
        self::fieldScript
    );
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
        if ($this->viewIdentifier === self::coreViewCreationId) {
            $document = $this->createDocument($resourceId);
        } else {
            $document = $this->getDocument($resourceId);
        }
        if (!in_array($this->viewIdentifier, array(
            self::coreViewCreationId,
            self::defaultViewConsultationId,
            self::defaultViewEditionId,
            self::coreViewConsultationId,
            self::coreViewEditionId
        )) && !$document->cvid) {
            $exception = new Exception("CRUDUI0001", $this->viewIdentifier, $resourceId);
            $exception->setHttpStatus("404", "View not found");
            throw $exception;
        }
        
        $info = array(
            "uri" => $this->getUri($document, $this->viewIdentifier)
        );
        /**
         * @var \Cvdoc $controlView
         */
        $controlView = DocManager::getDocument($document->cvid);
        
        $vid = $this->viewIdentifier;
        $info["view"] = $this->getViewInformation($document, $vid);
        if ($vid === "") {
            $coreViews = $this->getCoreViews($document);
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
            }
        } else {
            $viewInfo = $controlView->getView($vid);
            $info["properties"] = $this->getViewProperties($controlView, $viewInfo);
        }
        return $info;
    }
    /**
     * Update the ressource
     * @param string|int $resourceId Resource identifier
     * @throws Exception
     * @return mixed
     */
    public function update($resourceId)
    {
        
        if ($this->viewIdentifier != self::coreViewEditionId) {
            $document = $this->getDocument($resourceId);
            // apply specified mask
            if (($this->viewIdentifier != self::defaultViewEditionId) && ($document->cvid > 0)) {
                // special controlled view
                
                /**
                 * @var \CVDoc $cvdoc
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
                $mask = $tview[cvrender::cv_mskid];
                if ($mask) {
                    $document->setMask($mask); // apply mask to avoid modification of invisible attribute
                    
                }
            } else if ($document->cvid > 0) {
                $document->setMask($document::USEMASKCVEDIT);
            }
        }
        
        $documentData = new DocumentCrud();
        $documentData->setContentParameters($this->contentParameters);
        $documentData->update($resourceId);
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
            "requestIdentifier"=>$this->viewIdentifier,
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
            "requestIdentifier"=>$this->viewIdentifier,
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
            "requestIdentifier"=>$this->viewIdentifier,
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
     * @param \CVDoc $controlView
     * @param array $viewInfo
     * @return array
     */
    protected function getViewProperties(\CVDoc $controlView, array $viewInfo)
    {
        $viewId = $viewInfo[Cvrender::cv_idview];
        return array(
            "requestIdentifier"=>$this->viewIdentifier,
            "identifier" => $viewId,
            "mode" => ($viewInfo[Cvrender::cv_kview] === "VCONS") ? "consultation" : "edition",
            "label" => $controlView->getLocaleViewLabel($viewId) ,
            "isDisplayable" => ($viewInfo[Cvrender::cv_displayed] === "yes") ,
            "order" => intval($viewInfo[Cvrender::cv_order]) ,
            self::fieldMenu => $controlView->getLocaleViewMenu($viewId),
            "mask" => array(
                "id" => intval($viewInfo[Cvrender::cv_mskid]) ,
                "title" => $viewInfo[Cvrender::cv_msk]
            )
        );
    }
    /**
     * @param $document
     * @param $viewId
     * @return array
     * @throws Exception
     */
    protected function getViewInformation($document, &$viewId)
    {
        $config = $this->getRenderConfig($document, $viewId);
        $fields = $this->getFields();
        
        $viewInfo = array();
        foreach ($fields as $field) {
            switch ($field) {
                case self::fieldRenderOptions:
                    $viewInfo[self::fieldRenderOptions] = $config->getOptions($document)->jsonSerialize();
                    $viewInfo[self::fieldRenderOptions]["visibilities"] = $config->getVisibilities($document)->jsonSerialize();
                    break;

                case self::fieldRenderLabel:
                    $viewInfo[self::fieldRenderLabel] = $config->getLabel();
                    break;

                case self::fieldMenu:
                    $viewInfo[self::fieldMenu] = $config->getMenu($document);
                    break;

                case self::fieldTemplate:
                    $viewInfo[self::fieldTemplate] = $this->renderTemplates($config, $document);
                    break;

                case self::fieldDocumentData:
                    $viewInfo[self::fieldDocumentData] = $this->renderDocument($document);
                    break;

                case self::fieldLocale:
                    $viewInfo[self::fieldLocale] = $this->getLocaleData();
                    break;

                case self::fieldStyle:
                    $viewInfo[self::fieldStyle] = $this->getStyleData($config, $document);
                    break;

                case self::fieldScript:
                    $viewInfo[self::fieldScript] = $this->getScriptData($config, $document);
                    break;
            }
        }
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
     * @param $document
     * @return mixed
     */
    protected function renderDocument($document)
    {
        $documentData = new DocumentCrud();
        $fields = "document.attributes, document.properties.family, document.properties.icon, document.properties.status, document.properties.revision";
        if ($document->doctype !== "C") {
            $fields.= ",family.structure";
        }
        $documentData->setDefaultFields($fields);
        return $documentData->getInternal($document);
    }
    /**
     * Get the current document
     *
     * @param $resourceId
     * @return \Doc
     * @throws Exception
     * @throws \Dcp\HttpApi\V1\DocManager\Exception
     */
    protected function getDocument($resourceId)
    {
        if ($this->revision === - 1) {
            $document = DocManager::getDocument($resourceId);
            DocManager::cache()->addDocument($document);
        } else {
            $revId = DocManager::getRevisedDocumentId($resourceId, $this->revision);
            $document = DocManager::getDocument($revId, false);
        }
        if ($document === null) {
            $exception = new Exception("CRUD0200", $resourceId);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }
        
        return $document;
    }
    
    protected function createDocument($resourceId)
    {
        $document = DocManager::createDocument($resourceId);
        
        if ($document === null) {
            $exception = new Exception("CRUD0200", $resourceId);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }
        $document->title = sprintf(___("%s Creation", "ddui") , $document->getFamilyDocument()->getTitle());
        return $document;
    }
    /**
     * @param \Dcp\Ui\IRenderConfig $config
     * @param \Doc $document
     * @return string
     */
    protected function renderTemplates(\Dcp\Ui\IRenderConfig $config, \Doc $document)
    {
        $templates = $config->getTemplates($document);
        $delimiterStartTag = '[[';
        $delimiterEndTag = ']]';
        $option = array(
            'cache' => DEFAULT_PUBDIR . '/var/cache/mustache',
            'cache_file_mode' => 0600,
            'cache_lambda_templates' => true
        );
        $me = new \Mustache_Engine($option);
        
        $fl = new \Dcp\Ui\MustacheLoaderSection($templates, $delimiterStartTag, $delimiterEndTag);
        $fl->setDocument($document);
        $me->setPartialsLoader($fl);
        $delimiter = sprintf('{{=%s %s=}}', $delimiterStartTag, $delimiterEndTag);
        $docController = $config->getContextController($document);
        
        $mainTemplate = "[[>templates]]";
        return json_decode($me->render($delimiter . $mainTemplate, $docController));
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
        
        return $fields;
    }
    /**
     * @param \Doc $document
     * @param string $vid
     * @return \Dcp\Ui\IRenderConfig
     * @throws Exception
     * @throws \Dcp\Ui\Exception
     */
    protected function getRenderConfig(\Doc $document, &$vid)
    {
        $renderMode = "view";
        if ($vid == self::defaultViewConsultationId) {
            $renderMode = \Dcp\Ui\RenderConfigManager::ViewMode;
            $vid = '!none';
        } elseif ($vid == self::defaultViewEditionId) {
            $renderMode = \Dcp\Ui\RenderConfigManager::EditMode;
            $vid = '!none';
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
        if ($vid === "!none") {
            $config = \Dcp\Ui\RenderConfigManager::getDefaultFamilyRenderConfig($renderMode, $document);
            $vid = '';
        } else {
            $config = \Dcp\Ui\RenderConfigManager::getRenderConfig($renderMode, $document, $vid);
        }
        switch ($config->getType()) {
            case "view":
                $err = $document->control("view");
                if ($err) {
                    $exception = new Exception("CRUD0201", $this->resourceIdentifier, $err);
                    $exception->setHttpStatus("403", "Forbidden");
                    throw $exception;
                }
                break;

            case "edit":
                if ($document->locked == - 1) {
                    throw new Exception("CRUDUI0005", $vid);
                }
                if ($renderMode === "create") {
                    
                    $err = $document->control("icreate");
                    $err.= $document->control("create");
                    if ($err) {
                        $exception = new Exception("CRUD0201", $this->resourceIdentifier, $err);
                        $exception->setHttpStatus("403", "Forbidden");
                        throw $exception;
                    }
                } else {
                    $err = $document->canEdit();
                    if ($err) {
                        $exception = new Exception("CRUD0201", $this->resourceIdentifier, $err);
                        $exception->setHttpStatus("403", "Forbidden");
                        throw $exception;
                    }
                }
        }
        return $config;
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
        $result = array();
        $sql = sprintf("select id, revdate, cvid, views, fromid from docread where id = %d", $id);
        simpleQuery(getDbAccess() , $sql, $result, false, true);
        $user = getCurrentUser();
        $result[] = $user->id;
        $result[] = $user->memberof;
        
        $sql = sprintf("select revdate from docfam where id = %d", $result["fromid"]);
        simpleQuery(getDbAccess() , $sql, $familyRevdate, true, true);
        $result[] = $familyRevdate;
        
        if ($result["cvid"]) {
            $sql = sprintf("select revdate from docread where id = %d", $result["cvid"]);
            simpleQuery(getDbAccess() , $sql, $cvDate, true, true);
            $result[] = $cvDate;
        }
        // Necessary only when use family.structure
        $result[] = \ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
        return join(" ", $result);
    }
    
    public function analyseJSON($jsonString)
    {
        $values = DocumentUtils::analyzeDocumentJSON($jsonString);
        return $values;
    }
}
