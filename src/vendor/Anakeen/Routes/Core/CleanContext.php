<?php

namespace Anakeen\Routes\Core;

use Anakeen\Script\ShellManager;

/*
 * @note    No use by any route
 */
class CleanContext
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $complete = $request->getQueryParam("complete") === "true";

        sleep(5);
        $cmd = ShellManager::getAnkCmd() . " --script=cleanContext" ;

        if ($complete === true) {
            $cmd.= " --real=yes";
        }
        exec($cmd . " 2>&1", $output, $return);


        return $response->write(implode("\n", $output));
    }
}
