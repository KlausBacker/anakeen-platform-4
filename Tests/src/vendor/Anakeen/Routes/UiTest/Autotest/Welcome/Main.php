<?php

namespace Anakeen\Routes\UiTest\Autotest\Welcome;

class Main
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page = __DIR__ . "/welcome.html";
        $template = file_get_contents($page);
        return $response->write($template);
    }
}