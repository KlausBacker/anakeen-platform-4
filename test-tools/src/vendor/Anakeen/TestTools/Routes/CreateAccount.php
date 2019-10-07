<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\SmartElementManager;
use Anakeen\Core\AccountManager;

class CreateAccount
{

    const USER_TYPE = "user";
    const GROUP_TYPE = "group";
    const ROLE_TYPE = "role";

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
        $requestData = $request->getParsedBody();
        switch ($requestData['type']) {
            case self::USER_TYPE:
                $smartElement = $this->createUser($requestData);
                break;
            case self::GROUP_TYPE:
                $smartElement = $this->createGroup($requestData);
                break;
            case self::ROLE_TYPE:
                $smartElement = $this->createRole($requestData);
                break;
        }
        if (!empty($smartElement)) {
            $err = $smartElement->store();
            if (!empty($err)) {
                $exception = new Exception("ANKTEST007", $smartElement->getRawValue("us_login"), $err);
                $exception->setHttpStatus("500", "Cannot create account");
                throw $exception;
            }
            if (isset($requestData["tag"])) {
                $smartElement->addATag("ank_test", $requestData["tag"]);
            }
            if (isset($requestData['users']) && $requestData['type'] === self::GROUP_TYPE) {
                $this->addUsersToGroup($smartElement, $requestData['users']);
            }
        } else {
            $exception = new Exception("ANKTEST007", $smartElement->getRawValue("us_login"));
            $exception->setHttpStatus("500", "Cannot create account");
            throw $exception;
        }
        return ApiV2Response::withData($response, AccountInfos::formatAccount($smartElement->getAccount()));
    }

    protected function createUser($requestData)
    {
        $user = SmartElementManager::createDocument("IUSER");

        if (isset($requestData['login'])) {
            $user->setValue("us_login", $requestData['login']);
        }
        if (isset($requestData['lastname'])) {
            $user->setValue("us_lname", $requestData['lastname']);
        }
        if (isset($requestData['firstname'])) {
            $user->setValue("us_fname", $requestData['firstname']);
        }
        if (isset($requestData['password'])) {
            $user->setValue("us_passwd1", $requestData['password']);
            $user->setValue("us_passwd2", $requestData['password']);
        }
        return $user;
    }

    protected function createGroup($requestData)
    {
        $group = SmartElementManager::createDocument("IGROUP");

        if (isset($requestData['login'])) {
            $group->setValue("us_login", $requestData['login']);
        }
        if (isset($requestData['lastname'])) {
            $group->setValue("grp_name", $requestData['lastname']);
        }
        return $group;
    }
    protected function addUsersToGroup($group, $users)
    {
        if (isset($users) && is_array($users)) {
            foreach ($users as $userLogin) {
                $userAccount = AccountManager::getAccount($userLogin);

                if (!empty($userAccount) && ($userAccount->accounttype === "U" || $userAccount->accounttype === "G")) {
                    $err = $group->insertDocument($userAccount->fid);
                    if (!empty($err)) {
                        $exception = new Exception("ANKTEST006", $userAccount->login);
                        $exception->setHttpStatus("500", "Cannot add user to group");
                        throw $exception;
                    }
                }
            }
        }
    }
    protected function createRole($requestData)
    {
        $create = SmartElementManager::createDocument("ROLE");

        if (isset($requestData['login'])) {
            $create->setValue("role_login", $requestData['login']);
        }
        if (isset($requestData['lastname'])) {
            $create->setValue("role_name", $requestData['lastname']);
        }
        return $create;
    }
}
