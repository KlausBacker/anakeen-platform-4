<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Anakeen\Routes\Authent;


class LoginPage
{
    /**
     * Return all visible documents
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     *
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $page=__DIR__."/LoginPage.html";
        $response->write(file_get_contents($page));
        return $response;
    }

}