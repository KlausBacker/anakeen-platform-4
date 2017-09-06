<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Created by PhpStorm.
 * User: eric
 * Date: 15/09/14
 * Time: 15:04
 */

namespace Dcp\Ui;
use Dcp\HttpApi\V1\DocManager\DocManager;
class RenderConfigManager
{
    
    const ViewMode = "view";
    const EditMode = "edit";
    const CreateMode = "create";
    /**
     * @param $mode
     * @param \Doc $document
     * @param string $vId
     * @throws Exception
     * @return IRenderConfig
     */
    public static function getRenderConfig($mode, \Doc $document, &$vId = '')
    {
        if (empty($vId)) {
            if ($document->doctype === "C") {
                /**
                 * @var \DocFam $document
                 */
                return self::getFamilyRenderConfig($mode, $document);
            } else {
                return self::getDocumentRenderConfig($mode, $document, $vId);
            }
        }
        // try to get special vId
        if (empty($document->cvid)) {
            throw new Exception("UI0301", $vId, $document->getTitle());
        }
        $cvDoc = DocManager::getDocument($document->cvid);
        
        if ($cvDoc == null) {
            throw new Exception("UI0302", $document->cvid);
        }
        
        if (!is_a($cvDoc, "\\Dcp\\Family\\CVDoc")) {
            throw new Exception("UI0303", $cvDoc->getTitle());
        }
        /**
         * @var \Dcp\Family\CVDoc $cvDoc
         */
        $cvDoc->set($document);
        $err = $cvDoc->control($vId); // control special view
        if (!empty($err)) {
            $e = new Exception("UI0304", $vId, $document->getTitle());
            $e->addHttpHeader('HTTP/1.0 403 Forbidden');
            throw $e;
        }
        /**
         * @var \Dcp\Family\CVDoc $cvDoc
         */
        $vidInfo = $cvDoc->getView($vId);
        if (empty($vidInfo)) {
            throw new Exception("UI0305", $vId, $cvDoc->getTitle());
        }
        if (!$cvDoc->isValidView($vidInfo, true)) {
            throw new Exception("UI0308", $vId, $cvDoc->getTitle());
        }
        
        return self::getRenderFromVidinfo($vidInfo, $document);
    }
    
    protected static function getRenderFromVidinfo(array $vidInfo, \Doc $document)
    {
        
        $mskId = $vidInfo[\Dcp\AttributeIdentifiers\Cvdoc::cv_mskid];
        if ($mskId) {
            $err = $document->setMask($mskId);
            if ($err) {
                addWarningMsg($err);
            }
        }
        
        $renderClass = isset($vidInfo[\Dcp\AttributeIdentifiers\Cvdoc::cv_renderconfigclass]) ? $vidInfo[\Dcp\AttributeIdentifiers\Cvdoc::cv_renderconfigclass] : null;
        if ($renderClass) {
            $rc = new $renderClass();
            if (!is_a($rc, "Dcp\\Ui\\IRenderConfig")) {
                throw new Exception("UI0306", $renderClass, "Dcp\\Ui\\IRenderConfig");
            }
            return $rc;
        } else {
            if ($vidInfo[\Dcp\AttributeIdentifiers\Cvdoc::cv_kview] === "VCONS") {
                $mode = self::ViewMode;
            } else {
                $mode = self::EditMode;
            }
            return self::getDefaultFamilyRenderConfig($mode, $document);
        }
    }
    /**
     * @param string $mode (view, edit, create)
     * @param \Doc   $document
     * @param string $vid view identifier
     *
     * @return IRenderConfig
     * @throws Exception
     */
    public static function getDocumentRenderConfig($mode, \Doc $document, &$vid = '')
    {
        if ($document->cvid > 0) {
            /**
             * @var \Dcp\Family\CVDoc $cvDoc
             */
            $cvDoc = DocManager::getDocument($document->cvid);
            return self::getRenderConfigCv($mode, $cvDoc, $document, $vid);
        }
        
        return self::getDefaultFamilyRenderConfig($mode, $document);
    }
    
    public static function getParameterRenderConfig($mode, \Doc $document)
    {
        $renderAccessClass = self::getRenderParameterAccess($document->fromname);
        if ($renderAccessClass) {
            /**
             * @var $access \Dcp\Ui\IRenderConfigAccess
             */
            $access = new $renderAccessClass();
            $config = $access->getRenderConfig($mode, $document);
            if ($config) {
                return $config;
            }
        }
        return null;
    }
    
    public static function getTransitionRender($transitionId, \WDoc $workflow)
    {
        $render = null;
        if (is_a($workflow, 'Dcp\Ui\IRenderTransitionAccess')) {
            /**
             * @var \WDoc|\Dcp\Ui\IRenderTransitionAccess $workflow
             */
            $render = $workflow->getTransitionRender($transitionId, $workflow);
            $render->setWorkflow($workflow);
        } else {
            $renderTransitionClass = self::getRenderParameter($workflow->fromname, "renderTransitionClass");
            if ($renderTransitionClass) {
                /**
                 * @var $access \Dcp\Ui\IRenderTransitionAccess
                 */
                $access = new $renderTransitionClass();
                $render = $access->getTransitionRender($transitionId, $workflow);
                $render->setWorkflow($workflow);
            } else {
                $render = new TransitionRender();
                $render->setWorkflow($workflow);
            }
        }
        
        return $render;
    }
    /**
     * Return render class name defined in RENDER_PARAMETERS application parameter
     * @param string $familyName family name
     *
     * @return string|null
     */
    public static function getRenderParameterAccess($familyName)
    {
        return self::getRenderParameter($familyName, "renderAccessClass");
    }
    /**
     * Return render class name defined in RENDER_PARAMETERS application parameter
     *
     * @param string $familyName family name
     * @param string $key [renderAccessClass, renderTransitionClass, disableTag, applyRefresh]
     *
     * @return null|string
     */
    public static function getRenderParameter($familyName, $key)
    {
        static $renderParameters = null;
        if ($renderParameters === null) {
            $renderParameters = \ApplicationParameterManager::getParameterValue("DOCUMENT", "RENDER_PARAMETERS");
            $renderParameters = json_decode($renderParameters, true);
        }
        if (isset($renderParameters["families"][$familyName][$key])) {
            return $renderParameters["families"][$familyName][$key];
        }
        return null;
    }
    /**
     * Get render designed by document class
     * @param $mode
     * @param \Doc $document
     * @throws Exception
     * @return IRenderConfig
     */
    public static function getDefaultFamilyRenderConfig($mode, \Doc $document)
    {
        $parameterRender = self::getParameterRenderConfig($mode, $document);
        if ($parameterRender) {
            return $parameterRender;
        }
        if (is_a($document, "Dcp\\Ui\\IRenderConfigAccess")) {
            /**
             * @var IRenderConfigAccess|\Doc $document
             */
            $renderConfig = $document->getRenderConfig($mode, $document);
            if ($renderConfig) {
                return $renderConfig;
            }
        }
        return self::getRenderDefaultConfig($mode);
    }
    /**
     * Get render of a family itself
     * @param $mode
     * @param \DocFam $family
     * @return FamilyView
     */
    protected static function getFamilyRenderConfig($mode, \DocFam $family)
    {
        
        return new \Dcp\Ui\FamilyView();
    }
    /**
     * @param string $mode
     * @throws Exception
     * @return IRenderConfig
     */
    public static function getRenderDefaultConfig($mode)
    {
        switch ($mode) {
            case self::ViewMode:
                return new \Dcp\Ui\DefaultView();
            case self::EditMode:
                return new \Dcp\Ui\DefaultEdit();
            case self::CreateMode:
                return new \Dcp\Ui\DefaultEdit();
            default:
                throw new Exception("UI0300", $mode);
        }
    }
    /**
     * @param string $mode view/edit/create
     * @param \Dcp\Family\CVDoc $cv
     * @param \Doc   $document
     * @param string $vid view identifier
     *
     * @return IRenderConfig
     * @throws Exception
     */
    public static function getRenderConfigCv($mode, \Dcp\Family\CVDoc $cv, \Doc $document, &$vid = '')
    {
        $cv->set($document);
        $renderAccessClass = $cv->getRawValue(\Dcp\AttributeIdentifiers\Cvdoc::cv_renderaccessclass);
        if ($renderAccessClass) {
            if ($renderAccessClass[0] !== '\\') {
                $renderAccessClass = '\\' . $renderAccessClass;
            }
            /**
             * @var $access \Dcp\Ui\IRenderConfigAccess
             */
            $access = new $renderAccessClass();
            $config = $access->getRenderConfig($mode, $document);
            if ($config) {
                return $config;
            }
        }
        $vidInfo = $document->getDefaultView(($mode === "edit" || $mode === "create") , "all");
        
        if ($vidInfo) {
            // vid already controlled by cv class
            $vid = $vidInfo[\Dcp\AttributeIdentifiers\Cvdoc::cv_idview];
            $rc = self::getRenderFromVidinfo($vidInfo, $document);
            if ($rc) {
                return $rc;
            }
        } else {
            switch ($mode) {
                case self::ViewMode:
                    $document->applyMask(\Doc::USEMASKCVVIEW);
                    break;

                case self::EditMode:
                    $document->applyMask(\Doc::USEMASKCVEDIT);
                    break;

                case self::CreateMode:
                    $document->applyMask(\Doc::USEMASKCVEDIT);
                    break;

                default:
                    throw new Exception("UI0300", $mode);
            }
        }
        return self::getDefaultFamilyRenderConfig($mode, $document);
    }
}
