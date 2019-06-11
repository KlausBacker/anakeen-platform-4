<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 10/10/18
 * Time: 15:16
 */

namespace Anakeen\Routes\Devel\UI;

use Anakeen\Router\Exception;
use Anakeen\SmartElementManager;
use Anakeen\Ui\UIGetAssetPath;

class Profile
{
    protected $componentProps = null;

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->componentProps = $request->getParam("options", null);
        if (!empty($this->componentProps)) {
            if ($this->componentProps["onlyExtendedAcls"] == "true") {
                $this->componentProps["onlyExtendedAcls"] = true;
            } else {
                $this->componentProps["onlyExtendedAcls"] = false;
            }
        }
    }

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        $page = __DIR__ . "/Layout/profile.html.mustache";
        $id = $args["id"];
        $se = SmartElementManager::getDocument($id);
        if (!$se->isAlive()) {
            throw new Exception("There is not smart element here $id");
        }
        $profileId = $se->profid;
        $mustache = new \Mustache_Engine();
        $data = [
            "title" => $se->getTitle(),
            "profileId" => $profileId,
            "CSS" => [
                [
                    "key" => "bootstrap",
                    "path" => UIGetAssetPath::getCssBootstrap()
                ],
                [
                    "key" => "kendo",
                    "path" => UIGetAssetPath::getCssKendo()
                ],
                [
                    "key" => "components",
                    "path" => UIGetAssetPath::getCssSmartWebComponents()
                ],
            ],
            "JS_DEPS" => [
                [
                    "key" =>"kendo",
                    "path" => UIGetAssetPath::getJSKendoPath()
                ],
                [
                    "key" => "kendoDLL",
                    "path" => UIGetAssetPath::getJSKendoComponentPath()
                ],
                [
                    "key" => "vueDll",
                    "path" => UIGetAssetPath::getJSVueComponentPath()
                ]
            ],
            "JS" => [
                [
                    "key" => "profile",
                    "path" => UIGetAssetPath::getElementAssets(
                        "DevCenterStandalone",
                        UIGetAssetPath::isInDebug() ? "dev" : "prod"
                    )["profile"]["js"]
                ]
            ]
        ];
        if (!empty($this->componentProps)) {
            $data["profileOptions"] = json_encode($this->componentProps);
        }
        $template = file_get_contents($page);
        return $response->write($mustache->render($template, $data));
    }
}
