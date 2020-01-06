<?php

namespace Anakeen\TestTools\Routes\Middleware;

use Anakeen\Core\AccountManager;
use Anakeen\Core\ContextManager;
use Anakeen\Router\Exception;
use Slim\Http\request;
use Slim\Http\response;

class TestToolsUserChange
{
    protected $login;

    public function __invoke(request $request, response $response, callable $next, array $args): response
    {
        $this->login = $request->getQueryParams()["login"] ?? null;
        if (!empty($this->login)) {
            $account = AccountManager::getAccount($this->login);
            if (null === $account) {
                throw new Exception(sprintf("No account for login \"%s\"", $this->login));
            }
            ContextManager::sudo($account);
        }
        return $next($request, $response);
    }
}
