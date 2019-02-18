<?php


namespace Anakeen\Routes\Admin\Tokens;

use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;

class TokenList
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initFilters($request);
        return ApiV2Response::withData($response, $this->doRequest());
    }


    protected function doRequest()
    {
        $sql = <<<'SQL'
select usertoken.*, ua.login as author, uc.login as user 
from usertoken, users ua, users uc 
where usertoken.authorid = ua.id and usertoken.userid = uc.id;
SQL;
        DbManager::query($sql, $tokens);

        return $tokens;
    }


    protected function initFilters(\Slim\Http\request $request)
    {
    }
}
