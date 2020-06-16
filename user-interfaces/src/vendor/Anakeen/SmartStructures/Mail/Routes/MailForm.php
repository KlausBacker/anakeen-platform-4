<?php

namespace Anakeen\SmartStructures\Mail\Routes;

use Anakeen\Router\Exception;
use Anakeen\Routes\Ui\DocumentHtml;

/**
 * @note use by /api/v2/ui/mail/form/{docid}.html
 */
class MailForm extends DocumentHtml
{
    /**
     * Send Document Html page
     *
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param array $args
     * @return \Slim\Http\response
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $targetId = $args["docid"];
        $args["docid"] = "MAIL";
        $args["view"] = "!defaultCreation";

        $queries = ($request->getQueryParams());

        $customClientData = [
            "targetDocument" => $targetId
        ];
        if (!empty($queries["mailTemplate"])) {
            $customClientData["mailTemplate"]["name"] = $queries["mailTemplate"];
        }

        if (!empty($queries["selink"])) {
            $customClientData["mailTemplate"]["selink"] = $queries["selink"];
        }
        if (!empty($queries["keys"])) {
            $customClientData["mailTemplate"]["keys"] = json_decode($queries["keys"],true);
        }

        $request = $request->withQueryParams(["customClientData" => json_encode($customClientData)]);

        return parent::__invoke($request, $response, $args);
    }
}