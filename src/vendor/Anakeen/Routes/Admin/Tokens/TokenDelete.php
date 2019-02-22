<?php


namespace Anakeen\Routes\Admin\Tokens;

use Anakeen\Core\DbManager;
use Anakeen\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\Routes\Core\Lib\ApiMessage;

/**
 * Class TokenDelete
 *
 * @note use by route DELETE /api/v2/admin/tokens/{token}
 */
class TokenDelete
{
    protected $token;
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);

        $message = new ApiMessage(sprintf("Token \"%s\" is deleted", $this->token));
        return ApiV2Response::withData($response, $this->doRequest(), [$message]);
    }


    protected function doRequest()
    {
        $token=new \UserToken("", $this->token);
        if (!$token->isAffected()) {
            $e=new Exception("Token deletion");
            $e->setUserMessage(sprintf("Token \"%s\" not exists", $this->token));
            throw $e;
        }

        $err=$token->delete();
        if ($err) {
            $e=new Exception("Token deletion");
            $e->setUserMessage($err);
            throw $e;
        }
        return [
            "token" => $this->token
        ];

    }


    protected function initParameters($args)
    {
        $this->token=$args["token"];
    }
}
