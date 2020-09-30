<?php
namespace Anakeen\SmartStructures\Task\Routes;

use Anakeen\Router\ApiV2Response;
use Anakeen\SmartStructures\Task\CrontabManager;

/**
 * Verify crontab syntax
 *
 * @note    Used by route : GET /api/v2/admin/task/crontab/{crontab}
 */
class CrontabCheck
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $crontab = $args["crontab"];

        $error = CrontabManager::getCrontabError($crontab);
        if ($error) {
            $e = new \Anakeen\Exception("crontab error");
            $e->setUserMessage($error);
            throw $e;
        }

        $data["human"] = CrontabManager::getHumanSchedule($crontab);
        $data["parts"] = CrontabManager::getCrontabParts($crontab);
        $data["dates"] = CrontabManager::getNextDates($crontab, 4, '%A %d %B %Y, %H:%M');

        return ApiV2Response::withData($response, $data);
    }
}
