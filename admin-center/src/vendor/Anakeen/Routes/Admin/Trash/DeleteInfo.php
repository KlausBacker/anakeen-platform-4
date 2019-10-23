<?php

namespace Anakeen\Routes\Admin\Trash;

use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use Anakeen\Routes\Core\DocumentData;
use Anakeen\Core\SEManager;
use Anakeen\Core\DbManager;

use DocRel;

/**
 *
 * Get info about element before deletion
 *
 * @note    Used by route : GET /api/v2/trash/{docid}
 */


class DeleteInfo
{
    protected $element;
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->documentId = $args["docid"];
        $this->element = SEManager::getDocument($this->documentId);

        if (!$this->element) {
            $exception = new Exception(sprintf("Element \"%s\" not exist", $this->$args["docid"]));
            throw $exception;
        }

        $relations = new DocRel();
        $relations->sinitid = $this->element->initid;

        $relatedElements = $relations->getIRelations();

        $tmp = '{ 
            "data":[]}';

        $arr = json_decode($tmp, true);


        foreach ($relatedElements as $item) {
            if ($item["doctype"] !== "Z" && $item["doctype"] !== "F") {
                array_push($arr['data'], $item);
            }
        };

        return ApiV2Response::withData($response, count($arr["data"]));


        // $nbRelatedElements = count($relatedElements);

        // $data = $nbRelatedElements;
        // $data = $result[0]["count"];

        // return ApiV2Response::withData($response, $data);
    }
}
