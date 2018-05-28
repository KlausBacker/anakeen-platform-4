<?php

namespace Anakeen\Routes\Admin\Account;


use Anakeen\Core\DocManager;

class Groups
{
    private static $nbUserGroupCache = "./nbUserGroupCache.json";

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args) {

        $filter = $request->getQueryParam("filter");

        $searchAccount = new \SearchAccount();
        $searchAccount->setTypeFilter(\SearchAccount::groupType);
        if ($filter !== null) {
            $searchAccount->addFilter("lastname ~* '%s' OR login ~* '%s'", preg_quote($filter));
        }

        $result = [];

        $cache = [];
        if (is_file(self::$nbUserGroupCache)) {
            $cache = json_decode(file_get_contents(self::$nbUserGroupCache), true);
        }

        foreach ($searchAccount->search() as $currentAccount) {
            /* @var $currentAccount \Anakeen\Core\Account */
            $revDate = DocManager::getDocumentProperties($currentAccount->fid, ["revdate"])["revdate"];
            if (!(isset($cache[$currentAccount->id]) && $cache[$currentAccount->id]["revdate"] === $revDate)) {
                $cache[$currentAccount->id]["revdate"] = $revDate;
                $cache[$currentAccount->id]["nbUser"] = count($currentAccount->getGroupUserList());
            }
            $result[$currentAccount->id] = [
                "login"=> $currentAccount->login,
                "id" => $currentAccount->fid,
                "accountId" => $currentAccount->id,
                "parents" => $currentAccount->getMemberOf(),
                "title" => getDocTitle($currentAccount->fid),
                "nbUser" => $cache[$currentAccount->id]["nbUser"]
            ];
        }

        file_put_contents(self::$nbUserGroupCache, json_encode($cache));

        return $response->withJson($result);

    }
}