<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\Core\AccountManager;
use Anakeen\SmartElementManager;
use SmartStructure\Igroup;

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
        return $response;
    }


    protected function addAccountToGroup(\Anakeen\Core\Account $group, $accountLogin)
    {
        $account = null;
        if (isset($accountLogin)) {
            $account = AccountManager::getAccount($accountLogin);
            if (!empty($account) && ($account->accounttype === "U" || $account->accounttype === "G")) {
                $seGroup = SmartElementManager::getDocument($group->fid);
                /** @var Igroup $seGroup  */
                if (!empty($seGroup)) {
                    $err = $seGroup->insertDocument($account->fid);
                    if (!empty($err)) {
                        $exception = new Exception("ANKTEST008", $accountLogin, $group->login);
                        $exception->setHttpStatus("500", "Cannot add user to group");
                        throw $exception;
                    }
                }
            }
        }
        return $account;
    }
    protected function removeAccountToGroup(\Anakeen\Core\Account $group, $accountLogin)
    {
        $account = null;
        if (isset($accountLogin)) {
            $account = AccountManager::getAccount($accountLogin);
            if (!empty($account) && ($account->accounttype === "U" || $account->accounttype === "G")) {
                $seGroup = SmartElementManager::getDocument($group->fid);
                if (!empty($seGroup)) {
                    /** @var Igroup $seGroup */
                    $err = $seGroup->removeDocument($account->fid);
                    
                    if (!empty($err)) {
                        $exception = new Exception("ANKTEST008", $accountLogin, $group->login);
                        $exception->setHttpStatus("500", "Cannot remove user from group");
                        throw $exception;
                    }
                }
            }
        }
        return $account;
    }
}
