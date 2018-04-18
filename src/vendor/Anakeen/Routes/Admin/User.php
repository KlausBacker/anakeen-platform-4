<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 18/04/18
 * Time: 15:01
 */
namespace Anakeen\Routes\Admin;


use Anakeen\Core\ContextManager;

class User
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $user = ContextManager::getCurrentUser();
        $return = [];
        if (!empty($user)) {
            $return = [
                "firstname" => $user->firstname,
                "lastname" => $user->lastname,
                "fid" => $user->fid,
                "id" => $user->id
            ];
        }
        return $response->withJson($return);
    }

}