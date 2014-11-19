<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
    public static function getRenderConfig($mode, \Doc $document, $vId = '')
    {
        if (empty($vId)) {
            if ($document->doctype === "C") {
                
                return self::getFamilyRenderConfig($mode, $document);
            } else {
                return self::getDocumentRenderConfig($mode, $document);
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
        
        if (!is_a($cvDoc, "CVDoc")) {
            throw new Exception("UI0303", $cvDoc->getTitle());
        }
        
        $err = $cvDoc->control($vId); // control special view
        if (!empty($err)) {
            $e = new Exception("UI0304", $vId, $document->getTitle());
            $e->addHttpHeader('HTTP/1.0 403 Forbidden');
            throw $e;
        }
        /**
         * @var \CVDoc $cvDoc
         */
        $vidInfo = $cvDoc->getView($vId);
        if (empty($vidInfo)) {
            throw new Exception("UI0305", $vId, $cvDoc->getTitle());
        }
        
        $rc = self::getRenderFromVidinfo($vidInfo, $document);
        if ($rc) {
            return $rc;
        }
        
        return self::getRenderDefaultConfig($mode);
    }
    
    protected static function getRenderFromVidinfo(array $vidInfo, \Doc $document)
    {
        
        $mskId = $vidInfo[\Dcp\AttributeIdentifiers\Cvrender::cv_mskid];
        if ($mskId) {
            $err = $document->setMask($mskId);
            if ($err) {
                addWarningMsg($err);
            }
        }
        
        $renderClass = isset($vidInfo[\Dcp\AttributeIdentifiers\Cvrender::cv_renderclass]) ? $vidInfo[\Dcp\AttributeIdentifiers\Cvrender::cv_renderclass] : null;
        if ($renderClass) {
            $rc = new $renderClass();
            if (!is_a($rc, "Dcp\\Ui\\IRenderConfig")) {
                throw new Exception("UI0306", $renderClass, "Dcp\\Ui\\IRenderConfig");
            }
            return $rc;
        }
        return null;
    }
    /**
     * @param $mode
     * @param \Doc $document
     * @throws Exception
     * @return IRenderConfig
     */
    public static function getDocumentRenderConfig($mode, \Doc $document)
    {
        if ($document->cvid > 0) {
            return self::getRenderConfigCv($mode, DocManager::getDocument($document->cvid) , $document);
        }
        
        return self::getDefaultFamilyRenderConfig($mode, $document);
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
        if (is_a($document, "Dcp\\Ui\\IRenderConfigAccess")) {
            /**
             * @var IRenderConfigAccess $document
             */
            $renderConfig = $document->getRenderConfig($mode);
            if ($renderConfig) {
                return $renderConfig;
            }
        }
        return self::getRenderDefaultConfig($mode);
    }
    
    protected static function getFamilyRenderConfig($mode, \DocFam $family)
    {
        
        return new \Dcp\Ui\FamilyView();
    }
    /**
     * @param $mode
     * @param \Doc $document
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
     * @param \CVDoc $cv
     * @param \Doc $document
     * @throws Exception
     * @return IRenderConfig
     */
    public static function getRenderConfigCv($mode, \CVDoc $cv, \Doc $document)
    {
        $cv->set($document);
        
        $vidInfo = $document->getDefaultView(($mode === "edit") , "all");
        if ($vidInfo) {
            // vid already controlled by cv class
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
        return self::getRenderDefaultConfig($mode);
    }
}
