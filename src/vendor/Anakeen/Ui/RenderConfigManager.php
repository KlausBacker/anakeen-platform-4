<?php

namespace Anakeen\Ui;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\SmartStructures\Wdoc\WDocHooks;
use Anakeen\Ui\MaskManager;

class RenderConfigManager
{

    const ViewMode = "view";
    const EditMode = "edit";
    const CreateMode = "create";

    /**
     * @param                                     $mode
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @param string                              $vId
     * @throws Exception
     * @return IRenderConfig
     */
    public static function getRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document, &$vId = '')
    {
        if (empty($vId)) {
            if ($document->doctype === "C") {
                /**
                 * @var \Anakeen\Core\SmartStructure $document
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
        $cvDoc = SEManager::getDocument($document->cvid);

        if ($cvDoc == null) {
            throw new Exception("UI0302", $document->cvid);
        }

        if (!is_a($cvDoc, \Anakeen\Core\SEManager::getFamilyClassName("Cvdoc"))) {
            throw new Exception("UI0303", $cvDoc->getTitle());
        }

        SEManager::cache()->addDocument($cvDoc);
        /**
         * @var \SmartStructure\CVDoc $cvDoc
         */
        $cvDoc->set($document);
        $err = $cvDoc->control($vId); // control special view
        if (!empty($err)) {
            $e = new Exception("UI0304", $vId, $document->getTitle());
            $e->addHttpHeader('HTTP/1.0 403 Forbidden');
            throw $e;
        }
        /**
         * @var \SmartStructure\CVDoc $cvDoc
         */
        $vidInfo = $cvDoc->getView($vId);
        if (empty($vidInfo)) {
            throw new Exception("UI0305", $vId, $cvDoc->getTitle());
        }


        return self::getRenderFromVidinfo($vidInfo, $document);
    }

    protected static function getRenderFromVidinfo(array $vidInfo, \Anakeen\Core\Internal\SmartElement $document)
    {
        $renderClass = isset($vidInfo[\SmartStructure\Fields\Cvdoc::cv_renderconfigclass]) ? $vidInfo[\SmartStructure\Fields\Cvdoc::cv_renderconfigclass] : null;
        if ($renderClass) {
            $rc = new $renderClass();
            if (!is_a($rc, IRenderConfig::class)) {
                throw new Exception("UI0306", $renderClass, IRenderConfig::class);
            }
            return $rc;
        } else {
            if ($vidInfo[\SmartStructure\Fields\Cvdoc::cv_kview] === "VCONS") {
                $mode = self::ViewMode;
            } else {
                $mode = self::EditMode;
            }
            return self::getDefaultFamilyRenderConfig($mode, $document);
        }
    }

    /**
     * @param string                              $mode (view, edit, create)
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @param string                              $vid  view identifier
     *
     * @return IRenderConfig
     * @throws Exception
     */
    public static function getDocumentRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document, &$vid = '')
    {
        if ($document->cvid > 0) {
            /**
             * @var \SmartStructure\CVDoc $cvDoc
             */
            $cvDoc = SEManager::getDocument($document->cvid);
            SEManager::cache()->addDocument($cvDoc);
            return self::getRenderConfigCv($mode, $cvDoc, $document, $vid);
        }

        return self::getDefaultFamilyRenderConfig($mode, $document);
    }

    public static function getParameterRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        $renderAccessClass = self::getRenderParameterAccess($document->fromname);
        if ($renderAccessClass) {
            /**
             * @var $access \Anakeen\Ui\IRenderConfigAccess
             */
            $access = new $renderAccessClass();
            $config = $access->getRenderConfig($mode, $document);
            if ($config) {
                return $config;
            }
        }
        return null;
    }

    public static function getTransitionRender($transitionId, WDocHooks $workflow)
    {
        $render = null;
        if (is_a($workflow, 'Anakeen\Ui\IRenderTransitionAccess')) {
            /**
             * @var WDocHooks|\Anakeen\Ui\IRenderTransitionAccess $workflow
             */
            $render = $workflow->getTransitionRender($transitionId, $workflow);
            $render->setWorkflow($workflow);
        } else {
            $renderTransitionClass = self::getRenderParameter($workflow->fromname, "renderTransitionClass");
            if ($renderTransitionClass) {
                /**
                 * @var $access \Anakeen\Ui\IRenderTransitionAccess
                 */
                $access = new $renderTransitionClass();
                $render = $access->getTransitionRender($transitionId, $workflow);
                if (!$render) {
                    $render = new TransitionRender();
                }
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
     * @param string $key        [renderAccessClass, renderTransitionClass, disableTag, applyRefresh]
     *
     * @return null|string
     */
    public static function getRenderParameter($familyName, $key = null)
    {
        static $renderParameters = null;
        if ($renderParameters === null) {
            $renderParameters = ContextManager::getParameterValue("Ui", "RENDER_PARAMETERS");
            $renderParameters = json_decode($renderParameters, true);
        }
        if ($key === null) {
            if (isset($renderParameters["families"][$familyName])) {
                return $renderParameters["families"][$familyName];
            }
        } elseif (isset($renderParameters["families"][$familyName][$key])) {
            return $renderParameters["families"][$familyName][$key];
        }


        return null;
    }

    public static function setRenderParameter($familyName, $key, $value)
    {
        $renderParameters = ContextManager::getParameterValue("Ui", "RENDER_PARAMETERS");
        $renderParameters = json_decode($renderParameters, true);
        $renderParameters["families"][$familyName][$key] = $value;

        ContextManager::setParameterValue("Ui", "RENDER_PARAMETERS", json_encode($renderParameters));
    }

    /**
     * Get render designed by document class
     * @param                                     $mode
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @throws Exception
     * @return IRenderConfig
     */
    public static function getDefaultFamilyRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
    {
        $parameterRender = self::getParameterRenderConfig($mode, $document);
        if ($parameterRender) {
            return $parameterRender;
        }
        if (is_a($document, \Anakeen\Ui\IRenderConfigAccess::class)) {
            /**
             * @var IRenderConfigAccess|SmartElement $document
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
     * @param                              $mode
     * @param \Anakeen\Core\SmartStructure $family
     * @return FamilyView
     */
    protected static function getFamilyRenderConfig($mode, \Anakeen\Core\SmartStructure $family)
    {

        return new \Anakeen\Ui\FamilyView();
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
                return new \Anakeen\Ui\DefaultView();
            case self::EditMode:
                return new \Anakeen\Ui\DefaultEdit();
            case self::CreateMode:
                return new \Anakeen\Ui\DefaultEdit();
            default:
                throw new Exception("UI0300", $mode);
        }
    }

    /**
     * @param string                              $mode view/edit/create
     * @param \SmartStructure\CVDoc               $cv
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @param string                              $vid  view identifier
     *
     * @return IRenderConfig
     * @throws Exception
     */
    public static function getRenderConfigCv($mode, \SmartStructure\CVDoc $cv, \Anakeen\Core\Internal\SmartElement $document, &$vid = '')
    {
        $cv->set($document);
        $renderAccessClass = $cv->getRawValue(\SmartStructure\Fields\Cvdoc::cv_renderaccessclass);
        if ($renderAccessClass) {
            if ($renderAccessClass[0] !== '\\') {
                $renderAccessClass = '\\' . $renderAccessClass;
            }
            /**
             * @var $access \Anakeen\Ui\IRenderConfigAccess
             */
            $access = new $renderAccessClass();
            $config = $access->getRenderConfig($mode, $document);
            if ($config) {
                return $config;
            }
        }
        $vidInfo = MaskManager::getDefaultView($document, ($mode === "edit" || $mode === "create"), "all");

        if ($vidInfo) {
            // vid already controlled by cv class
            $vid = $vidInfo[\SmartStructure\Fields\Cvdoc::cv_idview];
            $rc = self::getRenderFromVidinfo($vidInfo, $document);
            if ($rc) {
                return $rc;
            }
        }
        return self::getDefaultFamilyRenderConfig($mode, $document);
    }
}
