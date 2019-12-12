<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Core\Account;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Accounts\SearchAccounts;

class AccountInfos
{
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     */
    public function __invoke(
        \Slim\Http\request $request,
        \Slim\Http\response $response,
        $args
    ) {
        if (!empty($args['login'])) {
            $search = new SearchAccounts();
            $search->addFilter("login = '%s'", pg_escape_string($args['login']));
            $list = $search->search();

            $result = $list->current();
            $responseData = self::formatAccount($result);
            return ApiV2Response::withData($response, $responseData);
        } else {
            $exception = new Exception("ANKTEST006");
            $exception->setHttpStatus("400", "login is required");
            throw $exception;
        }
    }

    public static function formatAccount(Account $account)
    {
        return [
            "login" => $account->login,
            "id" => $account->id,
            "fid" => $account->fid,
            "type" => $account->accounttype,
            "roles" => $account->getAllRoles(),
            "groupes"=> $account->getAllMembers(),
            "data" => [
                "mail" => $account->mail,
                "firstname" => $account->firstname,
                "lastname" => $account->lastname
            ]
        ];
    }
}
