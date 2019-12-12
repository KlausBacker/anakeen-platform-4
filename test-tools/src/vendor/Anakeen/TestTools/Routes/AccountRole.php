<?php

namespace Anakeen\TestTools\Routes;

use Anakeen\Core\AccountManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

class AccountRole
{
    protected $request;
    protected $result;
    protected $role;
    /**
     * @var array|object|null
     */
    protected $requestData=[];

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
        
        $this->initParameters($request, $args);
        
        return ApiV2Response::withData($response, AccountInfos::formatAccount($this->result));
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $role = $args['role'] ?? null;
        if (empty($role)) {
            $exception = new Exception("ANKTEST004", 'role');
            $exception->setHttpStatus("400", "role identifier is required");
            throw $exception;
        }

        $this->role = AccountManager::getAccount($role);
        if (empty($this->role)) {
            $exception = new Exception("ANKTEST006", $role);
            $exception->setHttpStatus("500", "role doesn't exist");
            throw $exception;
        }

        $this->requestData = $request->getParsedBody();
        if (!isset($this->requestData['accountlogin'])) {
            $exception = new Exception("ANKTEST007", 'accountlogin', $this->role->login);
            $exception->setHttpStatus("400", "Parameter is required");
            throw $exception;
        }

        $this->result = null;
        switch ($request->getMethod()) {
            case "PUT":
                $this->result = $this->addRoleToAccount($this->requestData['accountlogin'], $this->role);
                break;
            case "DELETE":
                $this->result = $this->removeRoleToAccount($this->requestData['accountlogin'], $this->role);
                break;
        }
    }

    protected function addRoleToAccount($accountLogin, \Anakeen\Core\Account $role = null)
    {
        $account = null;
        if (isset($accountLogin)) {
            $account = AccountManager::getAccount($accountLogin);
            if (!empty($account) && ($account->accounttype === "U" || $account->accounttype === "G")) {
                $roles = $account->getRoles();
                array_push($roles, $role->id);
                $err = $account->setRoles($roles);
                if (!empty($err)) {
                    $exception = new Exception("ANKTEST008", $accountLogin, $role->login);
                    $exception->setHttpStatus("500", "Cannot add user to group");
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
                $accountRoles = $account->getRoles();
                $keepAccountRole = [];
                foreach ($accountRoles as $rId) {
                    if (($rId !== $role->id)) {
                        array_push($keepAccountRole, $rId);
                    }
                }
                $account->setRoles($keepAccountRole);
                if (!empty($err)) {
                    $exception = new Exception("ANKTEST008", $accountLogin, $role->login);
                    $exception->setHttpStatus("500", "Cannot remove account role");
                    throw $exception;
                }
            }
        }

        return $account;
    }
}
