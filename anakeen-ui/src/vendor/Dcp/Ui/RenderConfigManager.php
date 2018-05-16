<?php

namespace Dcp\Ui;

use Anakeen\Core\SEManager;
use Anakeen\SmartStructures\Wdoc\WDocHooks;

class RenderConfigManager
{

    const ViewMode = "view";
    const EditMode = "edit";
    const CreateMode = "create";

    /**
     * @param        $mode
     * @param \Anakeen\Core\Internal\SmartElement   $document
     * @param string $vId
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

        $mskId = $vidInfo[\SmartStructure\Attributes\Cvdoc::cv_mskid];
        if ($mskId) {
            $err = $document->setMask($mskId);
            if ($err) {
                addWarningMsg($err);
            }
        }

        $renderClass = isset($vidInfo[\SmartStructure\Attributes\Cvdoc::cv_renderconfigclass]) ? $vidInfo[\SmartStructure\Attributes\Cvdoc::cv_renderconfigclass] : null;
        if ($renderClass) {
            $rc = new $renderClass();
            if (!is_a($rc, "Dcp\\Ui\\IRenderConfig")) {
                throw new Exception("UI0306", $renderClass, "Dcp\\Ui\\IRenderConfig");
            }
            return $rc;
        } else {
            if ($vidInfo[\SmartStructure\Attributes\Cvdoc::cv_kview] === "VCONS") {
                $mode = self::ViewMode;
            } else {
                $mode = self::EditMode;
            }
            return self::getDefaultFamilyRenderConfig($mode, $document);
        }
    }

    /**
     * @param string $mode (view, edit, create)
     * @param \Anakeen\Core\Internal\SmartElement   $document
     * @param string $vid  view identifier
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
            return self::getRenderConfigCv($mode, $cvDoc, $document, $vid);
        }

        return self::getDefaultFamilyRenderConfig($mode, $document);
    }

    public static function getParameterRenderConfig($mode, \Anakeen\Core\Internal\SmartElement $document)
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

    public static function getTransitionRender($transitionId, WDocHooks $workflow)
    {
        $render = null;
        if (is_a($workflow, 'Dcp\Ui\IRenderTransitionAccess')) {
            /**
             * @var WDocHooks|\Dcp\Ui\IRenderTransitionAccess $workflow
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
    public static function getRenderParameter($familyName, $key)
    {
        static $renderParameters = null;
        if ($renderParameters === null) {
            $renderParameters = \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("DOCUMENT", "RENDER_PARAMETERS");
            $renderParameters = json_decode($renderParameters, true);
        }
        if (isset($renderParameters["families"][$familyName][$key])) {
            return $renderParameters["families"][$familyName][$key];
        }
        return null;
    }

    /**
     * Get render designed by document class
     * @param      $mode
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
     * @param                              $mode
     * @param \Anakeen\Core\SmartStructure $family
     * @return FamilyView
     */
    protected static function getFamilyRenderConfig($mode, \Anakeen\Core\SmartStructure $family)
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
     * @param string                $mode view/edit/create
     * @param \SmartStructure\CVDoc $cv
     * @param \Anakeen\Core\Internal\SmartElement                  $document
     * @param string                $vid  view identifier
     *
     * @return IRenderConfig
     * @throws Exception
     */
    public static function getRenderConfigCv($mode, \SmartStructure\CVDoc $cv, \Anakeen\Core\Internal\SmartElement $document, &$vid = '')
    {
        $cv->set($document);
        $renderAccessClass = $cv->getRawValue(\SmartStructure\Attributes\Cvdoc::cv_renderaccessclass);
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
        $vidInfo = $document->getDefaultView(($mode === "edit" || $mode === "create"), "all");

        if ($vidInfo) {
            // vid already controlled by cv class
            $vid = $vidInfo[\SmartStructure\Attributes\Cvdoc::cv_idview];
            $rc = self::getRenderFromVidinfo($vidInfo, $document);
            if ($rc) {
                return $rc;
            }
        } else {
            switch ($mode) {
                case self::ViewMode:
                    $document->applyMask(\Anakeen\Core\Internal\SmartElement::USEMASKCVVIEW);
                    break;

                case self::EditMode:
                    $document->applyMask(\Anakeen\Core\Internal\SmartElement::USEMASKCVEDIT);
                    break;

                case self::CreateMode:
                    $document->applyMask(\Anakeen\Core\Internal\SmartElement::USEMASKCVEDIT);
                    break;

                default:
                    throw new Exception("UI0300", $mode);
            }
        }
        return self::getDefaultFamilyRenderConfig($mode, $document);
    }
}
