<?php


namespace Control;


use Control\Internal\Context;

class MainPage
{

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response)
    {
        $data = $this->getModuleData();
        //Add asset
        $assets = json_decode(file_get_contents(__DIR__."/../../public/Anakeen/manifest/control/prod.json"), true);
        $data["mainCss"] = ".".$assets["control"]["css"];
        $data["mainJs"] = ".".$assets["control"]["js"];
        $mustache = new \Mustache_Engine();
        $tpl = file_get_contents(__DIR__ . "/main.html.mustache");
        $response->getBody()->write($mustache->render($tpl, $data));
        return $response;
    }


    protected function getModuleData()
    {
        $context = Context::getContext();
        $data["modules"] = $context->getInstalledModuleList(true);

        usort($data["modules"], function ($a, $b) {
            return strcmp($a->name, $b->name);
        });



        $data["notinstalled"] = $context->getAvailableModuleList(true);


        usort($data["notinstalled"], function ($a, $b) {
            return strcmp($a->name, $b->name);
        });

        foreach (Context::getControlParameters() as $key => $value) {
            $data["parameters"][] = [
                "key" => $key,
                "value" => $value
            ];
        }
        foreach (Context::getParameters() as $key => $value) {
            $data["moduleParameters"][] = [
                "key" => $key,
                "value" => $value
            ];
        }


        usort($data["parameters"], function ($a, $b) {
            return strcmp($a["key"], $b["key"]);
        });

        $data["repositories"] = Context::getRepositories();

        $data["version"] = Context::getVersion();
        $data["availableVersion"] = Context::getAvailableVersion()?:"No update detected";

        $data["phpinfo"] = Context::getPhpInfo();
        $data["serverPath"] = $context->root;

        $data["controlPath"] = Context::getControlPath();
        return $data;
    }
}
