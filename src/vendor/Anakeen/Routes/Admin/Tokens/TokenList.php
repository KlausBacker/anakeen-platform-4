<?php


namespace Anakeen\Routes\Admin\Tokens;

use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;

class TokenList
{
    protected $showExpired = true;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initFilters($request);
        return ApiV2Response::withData($response, $this->doRequest());
    }


    protected function doRequest()
    {
        $sqlPattern = <<<'SQL'
select usertoken.*, ua.login as author, uc.login as user 
from usertoken, users ua, users uc 
where usertoken.authorid = ua.id and usertoken.userid = uc.id and %s
order by usertoken.cdate desc;
SQL;
        if ($this->showExpired === false) {
            $filter="usertoken.expire > now()";
        } else {
            $filter="true";
        }
        $sql = sprintf($sqlPattern, $filter);
        DbManager::query($sql, $tokens);

        foreach ($tokens as &$token) {
            $token["expendable"] = $token["expendable"] === "t";
            $token["userid"] = intval($token["userid"]);
            $token["authorid"] = intval($token["authorid"]);
            $token["cdate"][10] = 'T';
            if (strlen($token["expire"]) > 10) {
                $token["expire"][10] = 'T';
            }
            $token["routes"]=unserialize($token["context"]);
        }
        return $tokens;
    }


    protected function initFilters(\Slim\Http\request $request)
    {
        $this->showExpired = $request->getParam("showExpired") === "true";
    }
}
