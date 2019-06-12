<?php

namespace Control\Api;


class Info
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'info';


    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response)
    {
        $data = $this->getData();
        return $response->withJson($data);
    }

    protected function getData()
    {
        $data = \Control\Internal\Info::getInfo();
        return $data;
    }

}