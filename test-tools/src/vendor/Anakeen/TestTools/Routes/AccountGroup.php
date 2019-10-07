<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\Core\AccountManager;
use Anakeen\SmartElementManager;

class AccountGroup
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
        if (!empty($args['group'])) {
            $account = AccountManager::getAccount($args['group']);
            if (empty($account)) {
                $exception = new Exception("ANKTEST006", $args['group']);
                $exception->setHttpStatus("500", "group doesn't exist");
                throw $exception;
            }
            $requestData = $request->getParsedBody();
            if (!isset($requestData['accountlogin'])) {
                $exception = new Exception("ANKTEST007", 'accountlogin', $account->login);
                $exception->setHttpStatus("400", "Parameter is required");
                throw $exception;
            }

            $result = null;
            switch ($request->getMethod()) {
                case "PUT":
                    $result = $this->addAccountToGroup($account, $requestData['accountlogin']);
                    break;
                case "DELETE":
                    $result = $this->removeAccountToGroup($account, $requestData['accountlogin']);
                    break;
            }
            return ApiV2Response::withData($response, AccountInfos::formatAccount($result));
        }
    }


    protected function addAccountToGroup(\Anakeen\Core\Account $group = null, $accountLogin)
    {
        $account = null;
        if (isset($accountLogin)) {
            $account = AccountManager::getAccount($accountLogin);
            if (!empty($account) && ($account->accounttype === "U" || $account->accounttype === "G")) {
                $seGroup = SmartElementManager::getDocument($group->fid);
                if (!empty($seGroup)) {
                    $err = $seGroup->insertDocument($account->fid);
                    if (!empty($err)) {
                        $exception = new Exception("ANKTEST008", $accountLogin, $group->getAttributeValue("us_login"));
                        $exception->setHttpStatus("500", "Cannot add user to group");
                        $exception->setUserMessage(err);
                        throw $exception;
                    }
                }
            }
        }
        return $account;
    }
    protected function removeAccountToGroup(\Anakeen\Core\Account $group = null, $accountLogin)
    {
        $account = null;
        if (isset($accountLogin)) {
            $account = AccountManager::getAccount($accountLogin);
            if (!empty($account) && ($account->accounttype === "U" || $account->accounttype === "G")) {
                $seGroup = SmartElementManager::getDocument($group->fid);
                if (!empty($seGroup)) {
                    $err = $seGroup->removeDocument($account->fid);
                    
                    if (!empty($err)) {
                        $exception = new Exception("ANKTEST008", $accountLogin, $group->getAttributeValue("us_login"));
                        $exception->setHttpStatus("500", "Cannot remove user from group");
                        $exception->setUserMessage(err);
                        throw $exception;
                    }
                }
            }
        }
        return $account;
    }
}
