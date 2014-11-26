<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
namespace Dcp\Ui\Crud;

use Dcp\HttpApi\V1\DocManager\DocManager as DocManager;

class View extends \Dcp\HttpApi\V1\Crud\Crud
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
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     * @return mixed
     */
    public function create()
    {
        $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot create a view with the API");
        throw $exception;
    }
    /**
     * Read a resource
     * @param string|int $resourceId Resource identifier
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
            $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUDUI0001", $this->viewIdentifier, $resourceId);
            $exception->setHttpStatus("404", "View not found");
            throw $exception;
        }
        
        $info = array(
            "uri" => $this->getUri($document, $this->viewIdentifier)
        );
        /**
         * @var \Cvdoc $cv
         */
        $cv = DocManager::getDocument($document->cvid);
        
        $vid = $this->viewIdentifier;
        $info["view"] = $this->getViewInformation($document, $vid);
        if ($vid === "") {
            $coreViews = $this->getCoreViews($document);
            if ($this->viewIdentifier === self::defaultViewConsultationId) {
                $info["properties"] = $coreViews[self::coreViewConsultationId];
            } elseif ($this->viewIdentifier === self::defaultViewEditionId) {
                $info["properties"] = $coreViews[self::coreViewEditionId];
            } elseif ($this->viewIdentifier === self::coreViewConsultationId) {
                $info["properties"] = $coreViews[self::coreViewEditionId];
            } elseif ($this->viewIdentifier === self::coreViewEditionId) {
                $info["properties"] = $coreViews[self::coreViewEditionId];
            } elseif ($this->viewIdentifier === self::coreViewCreationId) {
                $info["properties"] = $coreViews[self::coreViewCreationId];
            }
        } else {
            
            $viewInfo = $cv->getView($vid);
            $info["properties"] = $this->getViewProperties($cv, $viewInfo);
        }
        return $info;
    }
    
    protected function getCoreViews(\Doc $document)
    {
        $defaultConsultation = array(
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
            "uri" => $this->getUri($document, self::coreViewCreationId) ,
            "identifier" => self::coreViewCreationId,
            "mode" => "edition",
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
    
    protected function getViewProperties(\CVDoc $cv, array $viewInfo)
    {
        $vid = $viewInfo[\Dcp\AttributeIdentifiers\Cvrender::cv_idview];
        return array(
            "identifier" => $vid,
            "mode" => ($viewInfo[\Dcp\AttributeIdentifiers\Cvrender::cv_kview] === "VCONS") ? "consultation" : "edition",
            "label" => $cv->getLocaleViewLabel($vid) ,
            "isDisplayable" => ($viewInfo[\Dcp\AttributeIdentifiers\Cvrender::cv_displayed] === "yes") ,
            "order" => intval($viewInfo[\Dcp\AttributeIdentifiers\Cvrender::cv_order]) ,
            self::fieldMenu => $cv->getLocaleViewMenu($vid) ,
            "mask" => array(
                "id" => intval($viewInfo[\Dcp\AttributeIdentifiers\Cvrender::cv_mskid]) ,
                "title" => $viewInfo[\Dcp\AttributeIdentifiers\Cvrender::cv_msk]
            )
        );
    }
    
    protected function getViewInformation($document, &$vid)
    {
        $config = $this->getRenderConfig($document, $vid);
        $fields = $this->getFields();
        
        $viewInfo = array();
        foreach ($fields as $field) {
            switch ($field) {
                case self::fieldRenderOptions:
                    $viewInfo[self::fieldRenderOptions] = $config->getOptions($document)->jsonSerialize();
                    $viewInfo[self::fieldRenderOptions]["visibilities"] = $config->getVisibilities($document)->jsonSerialize();
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
     * @return array|bool
     */
    protected function getScriptData($config, $document)
    {
        $jsList = $config->getJsReferences($document);
        if (!is_array($jsList)) {
            throw new \Dcp\HttpApi\V1\Crud\Exception("CRUDUI0007");
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
    
    protected function getLocaleData()
    {
        $localeId = \ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
        $config = getLocaleConfig($localeId);
        return $config;
    }
    
    protected function renderDocument($document)
    {
        $documentData = new \Dcp\HttpApi\V1\Crud\Document();
        $fields = "document.attributes, document.properties.family, document.properties.icon, document.properties.status, document.properties.revision";
        if ($document->doctype !== "C") {
            $fields.= ",family.structure";
        }
        $documentData->setDefaultFields($fields);
        return $documentData->getInternal($document);
    }
    
    protected function getDocument($resourceId)
    {
        if ($this->revision === - 1) {
            $document = DocManager::getDocument($resourceId);
        } else {
            $revId = DocManager::getRevisedDocumentId($resourceId, $this->revision);
            $document = DocManager::getDocument($revId, false);
        }
        if ($document === null) {
            $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0200", $resourceId);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }
        
        return $document;
    }
    
    protected function createDocument($resourceId)
    {
        
        $document = DocManager::createDocument($resourceId);
        
        if ($document === null) {
            $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0200", $resourceId);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }
        $document->title=sprintf(___("%s Creation","ddui"),$document->getFamilyDocument()->getTitle());
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
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     * @return array
     */
    protected function getFields()
    {
        if (!empty($this->contentParameters["fields"])) {
            $parameterfields = $this->contentParameters["fields"];
            $fields = array_map("trim", explode(",", $parameterfields));
            foreach ($fields as $field) {
                if (!in_array($field, $this->fields)) {
                    throw new \Dcp\HttpApi\V1\Crud\Exception("CRUDUI0004", $field, implode(", ", $this->fields));
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
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     * @throws \Dcp\Ui\Exception
     */
    protected function getRenderConfig(\Doc $document, &$vid)
    {
        $renderMode = "view";
        if ($vid == self::defaultViewConsultationId) {
            $renderMode = \Dcp\Ui\RenderConfigManager::ViewMode;
            $vid = '';
        } elseif ($vid == self::defaultViewEditionId) {
            $renderMode = \Dcp\Ui\RenderConfigManager::EditMode;
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
                    $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0201", $this->resourceIdentifier, $err);
                    $exception->setHttpStatus("403", "Forbidden");
                    throw $exception;
                }
                break;

            case "edit":
                if ($document->locked == - 1) {
                    throw new \Dcp\HttpApi\V1\Crud\Exception("CRUDUI0005", $vid);
                }
                if ($renderMode === "create") {
                    
                    $err = $document->control("icreate");
                    $err.= $document->control("create");
                    if ($err) {
                        $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0201", $this->resourceIdentifier, $err);
                        $exception->setHttpStatus("403", "Forbidden");
                        throw $exception;
                    }
                } else {
                    $err = $document->canEdit();
                    if ($err) {
                        $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0201", $this->resourceIdentifier, $err);
                        $exception->setHttpStatus("403", "Forbidden");
                        throw $exception;
                    }
                }
        }
        return $config;
    }
    /**
     * Update the ressource
     * @param string|int $resourceId Resource identifier
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     * @return mixed
     */
    public function update($resourceId)
    {
        $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot update a view with the API");
        throw $exception;
    }
    /**
     * Delete ressource
     * @param string|int $resourceId Resource identifier
     * @throws \Dcp\HttpApi\V1\Crud\Exception
     * @return mixed
     */
    public function delete($resourceId)
    {
        $exception = new \Dcp\HttpApi\V1\Crud\Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot delete a view with the API");
        throw $exception;
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
}
