<?php

namespace Dcp\Routes;

use Dcp\Core\Settings;
use Dcp\HttpApi\V1\Crud\Response;
use Dcp\Router\ApiV2Response;

use Anakeen\Router\URLUtils;

class Document
{

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     */
    public static function get(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $docid = $args["docid"];
        $etag = "";

        $crudCall = function (&$crudObject) use ($request, $docid, &$etag) {
            $crudObject = new \Dcp\HttpApi\V1\Crud\Document();
            Response::initRequest($crudObject, ["identifier" => $docid]);
            $etag = $crudObject->getEtagInfo();
            if (!ApiV2Response::matchEtag($request, $etag)) {
                return $crudObject->read($docid);
            } else {
                return [];
            }
        };

        $response = Response::withCrud($request, $response, $crudCall);
        $response = ApiV2Response::withEtag($request, $response, $etag);

        return $response;
    }

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     */
    public static function put(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $docid = $args["docid"];

        $crudCall = function (&$crudObject) use ($docid) {
            $crudObject = new \Dcp\HttpApi\V1\Crud\Document();
            Response::initRequest($crudObject);
            return $crudObject->update($docid);
        };

        $response = Response::withCrud($request, $response, $crudCall);
        return $response;
    }

    /**
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     */
    public static function delete(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $docid = $args["docid"];

        $crudCall = function (&$crudObject) use ($docid) {
            $crudObject = new \Dcp\HttpApi\V1\Crud\Document();
            Response::initRequest($crudObject);
            return $crudObject->delete($docid);
        };

        $response = Response::withCrud($request, $response, $crudCall);
        return $response;
    }

    public static function getURI($document, $prefix = Settings::ApiV2)
    {
        if ($document) {
            if ($document->defDoctype === "C") {
                return URLUtils::generateURL(sprintf("%s/families/%s.json", $prefix, $document->name));
            } else {
                if ($document->doctype === "Z") {
                    return URLUtils::generateURL(sprintf("%s/trash/%s.json", $prefix, $document->initid));
                } else {
                    if ($document->locked == -1) {
                        return URLUtils::generateURL(
                            sprintf(
                                "%s/documents/%s/revisions/%d.json",
                                $prefix,
                                $document->initid,
                                $document->revision
                            )
                        );
                    } else {
                        return URLUtils::generateURL(
                            sprintf(
                                "%s/documents/%s.json",
                                $prefix,
                                $document->initid
                            )
                        );
                    }
                }
            }
        }
        return "";
    }
}
