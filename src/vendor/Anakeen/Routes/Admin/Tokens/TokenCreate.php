<?php


namespace Anakeen\Routes\Admin\Tokens;

use Anakeen\Core\AccountManager;
use Anakeen\Core\DbManager;
use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\AuthenticatorManager;
use Anakeen\Routes\Core\Lib\ApiMessage;

/**
 * Class TokenDelete
 *
 * @note use by route POST /api/v2/admin/tokens/
 */
class TokenCreate
{
    protected $tokenData = [];

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request);

        $data = $this->doRequest();
        $message = new ApiMessage(sprintf("Token \"%s\" is create", $data["token"]));
        return ApiV2Response::withData($response, $data, [$message]);
    }


    protected function doRequest()
    {
        $u = AccountManager::getAccount($this->tokenData["user"]);
        if (!$u) {
            $e = new Exception("Create token");
            $e->setUserMessage(sprintf("Token creation failed. User \"%s\" not exists", $this->tokenData["user"]));
            throw $e;
        }

        $routes=[];
        foreach ($this->tokenData["routes"] as $route) {
            $routes[]=[
                "pattern" => $route["pattern"],
                "methods" => [$route["method"]]
            ];
        }
        $token = AuthenticatorManager::getAuthorizationToken(
            $u,
            $routes,
            $this->tokenData["expirationDate"] === "infinity" ? -1 :new \DateTime($this->tokenData["expirationDate"]),
            $this->tokenData["expendable"],
            $this->tokenData["description"]
        );

        $uToken = new \UserToken("", $token);
        return $uToken->getValues();
    }


    protected function initParameters(\Slim\Http\request $request)
    {
        $this->tokenData = $request->getParsedBody();
    }
}
