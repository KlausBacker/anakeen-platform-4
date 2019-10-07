<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\Core\AccountManager;
use Anakeen\SmartElementManager;

class AccountRole
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
        if (!empty($args['role'])) {
            $role = AccountManager::getAccount($args['role']);
            if (empty($role)) {
                $exception = new Exception("ANKTEST006", $args['role']);
                $exception->setHttpStatus("500", "role doesn't exist");
                throw $exception;
            }
            $requestData = $request->getParsedBody();
            if (!isset($requestData['accountlogin'])) {
                $exception = new Exception("ANKTEST007", 'accountlogin', $role->login);
                $exception->setHttpStatus("400", "Parameter is required");
                throw $exception;
            }

            $result = null;
            switch ($request->getMethod()) {
                case "PUT":
                    $result = $this->addRoleToAccount($requestData['accountlogin'], $role);
                    break;
                case "DELETE":
                    $result = $this->removeRoleToAccount($requestData['accountlogin'], $role);
                    break;
            }
            return ApiV2Response::withData($response, AccountInfos::formatAccount($result));
        }
    }
    protected function addRoleToAccount($accountLogin, \Anakeen\Core\Account $role = null)
    {
        $account = null;
        if (isset($accountLogin)) {
            $account = AccountManager::getAccount($accountLogin);
            if (!empty($account) && ($account->accounttype === "U" || $account->accounttype === "G")) {
                error_log($role->login);
                $err = $account->addRole($role->login);
                if (!empty($err)) {
                    $exception = new Exception("ANKTEST008", $accountLogin, $role->getAttributeValue("us_login"));
                    $exception->setHttpStatus("500", "Cannot add user to group");
                    $exception->setUserMessage(err);
                    throw $exception;
                }
            }
        }

        return $account;
    }
    protected function removeRoleToAccount($accountLogin, \Anakeen\Core\Account $role = null)
    {
        $account = null;
        if (isset($accountLogin)) {
            $account = AccountManager::getAccount($accountLogin);
            if (!empty($account) && ($account->accounttype === "U" || $account->accounttype === "G")) {
                /**
                 * @var \Anakeen\SmartElement
                 */
                $seAccount = SmartElementManager::getDocument($account->fid);
                $allRoles = $seAccount->getAttributeValue("us_roles");
                $index = array_search($role->fid, $allRoles);
                if ($index !== false) {
                    $err = $seAccount->removeArrayRow("us_t_roles", $index);
                }
                if (!empty($err)) {
                    $exception = new Exception("ANKTEST008", $accountLogin, $role->getAttributeValue("us_login"));
                    $exception->setHttpStatus("500", "Cannot remove account role");
                    $exception->setUserMessage(err);
                    throw $exception;
                }
            }
        }

        return $account;
    }
}
