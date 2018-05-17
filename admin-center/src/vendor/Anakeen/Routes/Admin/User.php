<?php
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