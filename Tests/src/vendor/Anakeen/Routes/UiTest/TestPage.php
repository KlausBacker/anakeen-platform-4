<?php


namespace Anakeen\Routes\UiTest;

use Anakeen\Core\ContextManager;

class TestPage
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/TestPage.html";

        $action = ContextManager::getCurrentAction();
        $action->lay = new \Layout($page, $action);

        $action->parent->addJsRef(\Dcp\Ui\UIGetAssetPath::getJSJqueryPath());
        $action->parent->addJsRef(\Dcp\Ui\UIGetAssetPath::getJSKendoPath());
        $action->parent->addJsRef("components/dist/ank-components.js");
        $action->parent->addJsRef("apps/uitest/dist/TestPage.js");

        $action->parent->AddCssRef("css/ank/document/bootstrap.css");
        $action->parent->AddCssRef("css/ank/document/kendo.css");

        $action->lay->set("appicon", $action->parent->icon);

        return $response->write($action->lay->gen());
    }
}