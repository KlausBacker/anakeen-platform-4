<?php

namespace Anakeen\SmartStructures\Dsearch\Routes;

use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class Attributes
 * @note    Used by route : GET /api/v2/smartstructures/dsearch/attributes/{family}}
 * @package Anakeen\SmartStructures\Dsearch\Routes
 */
class Attributes
{
    protected $familyname;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        if (isset($args["family"])) {
            $this->familyname = $args["family"];
        }

        $etag = $this->getEtagInfo($this->familyname);
        $response = ApiV2Response::withEtag($request, $response, $etag);
        if (ApiV2Response::matchEtag($request, $etag)) {
            return $response;
        }

        $return = array();
        //Propriétés
        $internals = array(
            "title" => ___("doctitle", "searchui"),
            "revdate" => ___("revdate", "searchui"),
            "cdate" => ___("cdate", "searchui"),
            "revision" => ___("revision", "searchui"),
            "owner" => ___("id owner", "searchui"),
            "locked" => ___("id locked", "searchui"),
            "allocated" => ___("id allocated", "searchui"),
            "svalues" => ___("any values", "searchui")
        );

        if ($this->familyname) {
            $tmpDoc = SEManager::createTemporaryDocument($this->familyname);
        } else {
            $tmpDoc = SEManager::createTemporaryDocument(1);
        }
        foreach ($internals as $k => $v) {
            if ($k == "revdate") {
                $type = "date";
            } else {
                if ($k == "owner") {
                    $type = "uid";
                } else {
                    if ($k == "locked") {
                        $type = "uid";
                    } else {
                        if ($k == "allocated") {
                            $type = "uid";
                        } else {
                            if ($k == "cdate") {
                                $type = "date";
                            } else {
                                if ($k == "revision") {
                                    $type = "int";
                                } else {
                                    if ($k == "state") {
                                        $type = "docid";
                                    } else {
                                        $type = "text";
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $methods = $tmpDoc->getSearchMethods("__properties__", $type);

            $return[] = array(
                "id" => $k,
                "label" => $v,
                "type" => $type,
                "methods" => $methods,
                "parent" => array(
                    "id" => "__properties__",
                    "label" => ___("Properties", "searchui")
                )
            );
        }

        if ($this->familyname) {
            $fdoc = SEManager::getFamily($this->familyname);
            if (!$fdoc) {
                $exception = new Exception("CRUD0103", __METHOD__);
                $exception->setHttpStatus("404", "Family not found");

                throw $exception;
            }
            //Attributs
            $tmpDoc = SEManager::createTemporaryDocument($this->familyname);
            foreach ($fdoc->getNormalAttributes() as $myAttribute) {
                if ($myAttribute->type == "array" || $myAttribute->type == "password") {
                    continue;
                }
                $optSearchable = $myAttribute->getOption("searchcriteria", "");
                if ($optSearchable == "hidden" || $optSearchable == "restricted") {
                    continue;
                }
                $type = $myAttribute->type;
                if ($myAttribute->isMultiple()) {
                    $type = $myAttribute->type . "[]";
                }

                $methods = $tmpDoc->getSearchMethods($myAttribute->id, $type);
                $return[] = array(
                    "id" => $myAttribute->id,
                    "label" => $myAttribute->getLabel(),
                    "type" => $type,
                    "methods" => $methods,
                    "parent" => array(
                        "id" => $myAttribute->fieldSet->id,
                        "label" => $myAttribute->fieldSet->getLabel()
                    )
                );
            }

            if (isset($fdoc->wid)) {
                $return[] = array(
                    "id" => "state",
                    "methods" => [],
                    "label" => array(
                        ___("activity"),
                        ___("step"),
                        ___("state")
                    ),
                    "type" => "wid",
                    "parent" => array(
                        "id" => "workflow",
                        "label" => ___("workflow")
                    )
                );
            }
        }

        return ApiV2Response::withData($response, $return);
    }


    /**
     * Return etag info
     *
     * @param $familyId
     * @return null|string
     */
    public function getEtagInfo($familyId)
    {
        if ($familyId) {
            $result[] = $familyId;
        }
        $result[] = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
        $result[] = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("WVERSION");

        return implode(",", $result);
    }
}
