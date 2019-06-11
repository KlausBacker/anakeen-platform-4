<?php


namespace Anakeen\Routes\Ui;

use Anakeen\Router\ApiV2Response;
use Anakeen\Core\SEManager;
use Anakeen\SmartElementManager;

/**
 * Class SubMenu
 * @note    Used by route : POST /api/v2/smart-elements/{docid}/views/{view}/menus/{menu}
 * @package Anakeen\Routes\Ui
 */
class SubMenu
{
    /**
     * @var \Anakeen\Core\SmartStructure
     */
    protected $_family = null;


    /**
     * Get Submenu content
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     * @return \Slim\Http\response
     * @throws \Anakeen\Ui\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $data = $this->getMenuContent($args["docid"], $args["view"], $args["menu"]);
        return ApiV2Response::withData($response, $data);
    }

    protected function getMenuContent($documentId, $vId, $menuId)
    {
        $renderMode = "view";

        $doc = SmartElementManager::getDocument($documentId);

        if (!$doc) {
            throw new \Anakeen\Ui\Exception(sprintf(___("Document \"%s\" not found ", "ddui"), $documentId));
        }
        $err = $doc->control("view");
        if ($err) {
            throw new \Anakeen\Ui\Exception($err);
        }

        if ($vId && $vId[0] === "!") {
            $vId = '';
        }

        $config = \Anakeen\Ui\RenderConfigManager::getRenderConfig($renderMode, $doc, $vId);
        $menu = $config->getMenu($doc);
        /**
         * @var \Anakeen\Ui\DynamicMenu $element
         */
        $element = $menu->getElement($menuId);
        if (!$element) {
            throw new \Anakeen\Ui\Exception(sprintf(___("Menu id \"%s\" not found ", "ddui"), $menuId));
        }

        return $element->getContent();
    }
}
