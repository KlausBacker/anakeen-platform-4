<?php


namespace Control;


class MainPage
{

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response)
    {
        $data = $this->getModuleData();
        $mustache = new \Mustache_Engine();
        $tpl = file_get_contents(__DIR__ . "/main.html.mustache");
        $response->getBody()->write($mustache->render($tpl, $data));
        return $response;
    }


    protected function getModuleData()
    {

        $context = Context::getContext();
        $data["modules"] = $context->getInstalledModuleListWithUpgrade(true);

        usort($data["modules"], function ($a, $b) {
            return strcmp($a->name, $b->name);
        });

        $data["notinstalled"] = $context->getAvailableModuleList(true);


        usort($data["notinstalled"], function ($a, $b) {
            return strcmp($a->name, $b->name);
        });

        foreach (Context::getParameters() as $key => $value) {
            $data["parameters"][] = [
                "key" => $key,
                "value" => $value
            ];
        }


        usort($data["parameters"], function ($a, $b) {
            return strcmp($a["key"], $b["key"]);
        });

        $data["repositories"]=Context::getRepositories();

        $data["version"]=Context::getVersion();
        $data["availableVersion"]=Context::getAvailableVersion();
        $data["phpinfo"]=Context::getPhpInfo();
        $data["serverPath"]=$context->root;

        $data["controlPath"] = getenv('WIFF_ROOT');
        return $data;
    }
}
