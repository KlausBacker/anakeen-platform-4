<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Routes\Authent;

use Dcp\Core\ContextManager;

/**
 * Class Session
 * Create a user session
 *
 * @note    Used by route : POST /api/v2/authent/sessions/current
 * @package Anakeen\Routes\Authent
 */
class Logout
{
    const FAILDELAY = 2;

    /**
     * Create User Session after verify authentication
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response $response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {

        $action = ContextManager::getCurrentAction();
        $action->session->close();

        \AuthenticatorManager::closeAccess();
        $data=[];
        foreach (headers_list() as $header) {
            if (preg_match("/location:\s*(.*)/i", $header, $reg)) {
                $data["location"]=$reg[1];
            }
        }

        $data["basicAuthent"]= (get_class(\AuthenticatorManager::$auth) === \BasicAuthenticator::class);
        if ($data["basicAuthent"]) {
            $response = $response->withStatus(401);
        }

        return $response->withJson($data);
    }
}
