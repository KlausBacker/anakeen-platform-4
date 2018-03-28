<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Dcp\Ui\Crud;

use Dcp\HttpApi\V1\Crud\Exception;
use Anakeen\Core\DocManager as DocManager;
use Dcp\HttpApi\V1\Crud\Crud;

class Menu extends Crud
{
    protected $documentId;
    protected $attributeId;
    /**
     * Use Create but it is a GET
     * But data are requested in a $_POST because they are numerous
     *
     * @throws Exception
     * @return mixed
     */
    public function create()
    {
        
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot create menu element with the API");
        throw $exception;
    }
    /**
     * get submenu document
     *
     * @param string|int $resourceId Resource identifier
     *
     * @return mixed
     * @throws \Dcp\Ui\Exception
     */
    public function read($resourceId)
    {
        
        $documentId = $resourceId;
        $menuId = $this->urlParameters["menu"];
        $vId = View::defaultViewConsultationId;
        if (isset($this->contentParameters["viewId"])) {
            $vId = $this->contentParameters["viewId"];
        }
        
        $renderMode = "view";
        if (isset($this->contentParameters["mode"])) {
            $renderMode = $this->contentParameters["mode"];
        }
        
        $doc = DocManager::getDocument($documentId);
        
        if (!$doc) {
            throw new \Dcp\Ui\Exception(sprintf(___("Document \"%s\" not found ", "ddui") , $documentId));
        }
        $err = $doc->control("view");
        if ($err) {
            throw new \Dcp\Ui\Exception($err);
        }
        
        if ($vId && $vId[0] === "!") {
            $vId = '';
        }
        
        $config = \Dcp\Ui\RenderConfigManager::getRenderConfig($renderMode, $doc, $vId);
        $menu = $config->getMenu($doc);
        /**
         * @var \Dcp\Ui\DynamicMenu $element
         */
        $element = $menu->getElement($menuId);
        if (!$element) {
            throw new \Dcp\Ui\Exception(sprintf(___("Menu id \"%s\" not found ", "ddui") , $menuId));
        }
        
        return $element->getContent();
    }
    /**
     * Update the ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @throws Exception
     * @return mixed
     */
    public function update($resourceId)
    {
        
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot update menu element with the API");
        throw $exception;
    }
    /**
     * Delete ressource
     *
     * @param string|int $resourceId Resource identifier
     *
     * @throws Exception
     * @return mixed
     */
    public function delete($resourceId)
    {
        
        $exception = new Exception("CRUD0103", __METHOD__);
        $exception->setHttpStatus("405", "You cannot delete menu element with the API");
        throw $exception;
    }
}
