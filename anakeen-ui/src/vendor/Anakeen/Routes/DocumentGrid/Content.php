<?php

namespace Anakeen\Routes\DocumentGrid;

use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;
use Anakeen\Routes\Core\DocumentList;
use Anakeen\SmartElementManager;

class Content extends DocumentList
{
    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    protected $_collection = null;


    /**
     * Read a ressource
     * @param string|int $resourceId Resource identifier
     * @return mixed
     */
    public function getData()
    {
        $return = parent::getData();
        $searchDoc = $this->initSearchDoc();

        $return["resultTotal"] = $searchDoc->onlyCount();
        $searchDoc = $this->initSearchDoc(true);
        $return["resultFiltered"] = $searchDoc->onlyCount();

        //$return["debug"] = $searchDoc->getSearchInfo();

       // $return["uri"] = $this->generateURL(sprintf("documentGrid/content/%s", $this->_collection->getPropertyValue("id")));
        $return["searchContentUrl"] = sprintf("api/v1/documentGrid/content/%s", $this->_collection->getPropertyValue("id"));

        unset($return["properties"]);
        return $return;
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $collectionId = $args["collection"] ;

        $this->_collection = SmartElementManager::getDocument($collectionId);
        if (!$this->_collection) {
            $exception = new Exception("CRUD0200", $collectionId);
            $exception->setHttpStatus("404", "Collection not found (CRUD015)");
            throw $exception;
        }
        parent::initParameters($request, $args);
    }

    /**
     * @param bool $withCriteria
     *
     * @return \SearchDoc
     * @throws Exception
     */
    protected function initSearchDoc($withCriteria = false)
    {
        $searchDoc = new \SearchDoc("");
        $searchDoc->useCollection($this->_collection->getPropertyValue("id"));
        if ($withCriteria) {
            if (!is_a($this->_collection, '\SmartStructure\Ssearch')) {
                if (isset($this->contentParameters["filters"])) {
                    $this->generateFilters($searchDoc, json_decode($this->contentParameters["filters"], true));
                }
                if (isset($this->contentParameters["keyword"]) && !empty($this->contentParameters["keyword"])) {
                    $searchDoc->addFilter("title ILIKE '%%%s%%'", str_replace(['_', '%'], ['\\_', '\\%'], $this->contentParameters["keyword"]));
                }
            } else {
                $noFilter = new RecordReturnMessage();
                $noFilter->contentText = "No use native filter for specialized searches";
                $noFilter->type = $noFilter::NOTICE;
                $this->addMessage($noFilter);
            }
        }
        return $searchDoc;
    }

    /**
     * Prepare the searchDoc
     */
    protected function prepareSearchDoc()
    {
        $this->_searchDoc = $this->initSearchDoc(true);
        $this->_searchDoc->setObjectReturn();
    }

    /**
     * Modify attribute fields
     *
     * @return array
     * @throws \Dcp\Exception
     */
    protected function getAttributeFields()
    {
        $prefix = self::GET_ATTRIBUTE;
        $fields = $this->getFields();
        if ($this->hasFields(self::GET_ATTRIBUTES) || $this->hasFields(self::GET_ATTRIBUTE)) {
            $tmpDoc = null;
            if ($this->_collection->getAttributeValue("se_famid")) {
                $tmpDoc = SEManager::getFamily($this->_collection->getAttributeValue("se_famid"));
            }
            return \Anakeen\Routes\Core\Lib\DocumentUtils::getAttributesFields($tmpDoc, $prefix, $fields);
        }
        return array();
    }

    /**
     * Generate the filters
     *
     * @param \SearchDoc $search
     * @param            $filters
     *
     * @return void
     *
     * @throws Exception
     */
    protected function generateFilters(\SearchDoc & $search, array $filters)
    {
        if (!empty($filters)) {
            foreach ($filters as $currentFilter) {
                if (!empty($currentFilter["value"])) {
                    if ($currentFilter["type"] === "enum") {
                        $search->addFilter("%s ~ E'\\\\y%s\\\\y'", $currentFilter["id"], $currentFilter["value"]);
                        continue;
                    } else {
                        if ($currentFilter["type"] === "state") {
                            $search->addFilter("%s = '%s'", $currentFilter["id"], $currentFilter["value"]);
                            continue;
                        } else {
                            if ($currentFilter["type"] === "int" || $currentFilter["type"] === "double" || $currentFilter["type"] === "money") {
                                $currentFilter["value"] = trim($currentFilter["value"]);
                                if (preg_match("/^([>=<])\s*([0-9][0-9 ]+)$/", $currentFilter["value"], $reg)) {

                                    $search->addFilter("%s %s '%s'", $currentFilter["id"], $reg[1], str_replace(" ", "", $reg[2]));
                                } else {
                                    if (!is_numeric($currentFilter["value"])) {
                                        throw new Exception(sprintf(___("Filter \"%s\" is not a numeric value.\nYou can use single numeric for operator \"contains\" or filter like \"> 1234\" , \"< 1234\" or \"= 1234\"",
                                            "docgrid"), $currentFilter["value"]));
                                    }
                                    $search->addFilter("%s::text ~ '%s'", $currentFilter["id"], $currentFilter["value"]);
                                }
                                continue;
                            } else {
                                if ($currentFilter["type"] === "date") {
                                    $currentFilter["value"] = trim($currentFilter["value"]);
                                    $isoDate = '';
                                    if (!preg_match("@^[<=>0-9 /\-]*$@", $currentFilter["value"])) {
                                        throw new Exception(sprintf(___("Filter \"%s\" is not a date value.\nYou can use date operator \"contains\" or filter like \"> 23/11/2015\" , \"< 23/11/2015\" or \"= 23/11/2015\" or \"> 2015\" or \"< 2015\"",
                                            "docgrid"), $currentFilter["value"]));
                                    }

                                    if (preg_match("@^[>]\s*([0-9]{4})$@", $currentFilter["value"], $reg)) {
                                        // "> 2016 same as > 2016-12-31"
                                        $isoDate = sprintf("%04d-%02d-%02d", $reg[1], 12, 31);
                                    } elseif (preg_match("@^[<]\s*([0-9]{4})$@", $currentFilter["value"], $reg)) {
                                        // "< 2016 same as < 2016-01-01"
                                        $isoDate = sprintf("%04d-%02d-%02d", $reg[1], 1, 1);
                                    } elseif (preg_match("@^[>=<]?\s*([0-9]+)/([0-9]+)/([0-9]+)$@", $currentFilter["value"], $reg)) {
                                        $lang = \Anakeen\Core\Internal\ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
                                        if ($lang === "en_US") {
                                            $isoDate = sprintf("%04d-%02d-%02d", $reg[3], $reg[1], $reg[2]);
                                        } else {
                                            $isoDate = sprintf("%04d-%02d-%02d", $reg[3], $reg[2], $reg[1]);
                                        }
                                    } elseif (preg_match("@^[>=<]?\s*([0-9]+)-([0-9]+)-([0-9]+)$@", $currentFilter["value"], $reg)) {
                                        $isoDate = sprintf("%04d-%02d-%02d", $reg[1], $reg[2], $reg[3]);
                                    }

                                    if ($isoDate) {
                                        $op = "=";
                                        if (preg_match("@^([>=<])@", $currentFilter["value"], $reg)) {
                                            $op = $reg[1];
                                        }

                                        $search->addFilter("%s %s '%s'", $currentFilter["id"], $op, $isoDate);
                                    } else {
                                        $search->addFilter("%s::text ~ '%s'", $currentFilter["id"], $currentFilter["value"]);
                                    }
                                    continue;
                                } else {
                                    $filterWords = explode(" ", $currentFilter["value"]);
                                    foreach ($filterWords as $filterWord) {
                                        $filterWord = trim($filterWord);
                                        if ($filterWord) {
                                            $filterWord = preg_quote($filterWord);
                                            // Keep ^ to use begin with operator
                                            if (substr($filterWord, 0, 2) === '\\^') {
                                                $filterWord = substr($filterWord, 1);
                                            }
                                            $search->addFilter("%s ~* '%s'", $currentFilter["id"], $filterWord);
                                        }
                                    }
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Extract orderBy
     *
     * @return string
     * @throws Exception
     */
    protected function extractOrderBy()
    {
        if (empty($this->contentParameters["orderBy"])) {
            $orderBy = $this->_collection->getRawValue(\SmartStructure\Attributes\Report::rep_idsort);
            if (!$orderBy) {
                $orderBy = $this->_collection->getRawValue(\SmartStructure\Attributes\Dsearch::se_orderby);
            }
            if (!$orderBy) {
                $orderBy = "title";
            }

            $orderSens = $this->_collection->getRawValue(\SmartStructure\Attributes\Report::rep_ordersort, "asc");
            $orderBy .= ":" . $orderSens;
        } else {
            $orderBy = $this->contentParameters["orderBy"];
        }
        $famid = $this->_collection->getRawValue("se_famid");
        if ($famid) {
            $family = SEManager::getFamily($famid);
        } else {
            $family = null;
        }
        if ($family && preg_match("/^([a-z_01-9]+)(.*)$/", $orderBy, $reg)) {
            // use title for docid order by
            $oa = $family->getAttribute($reg[1]);
            if ($oa && ($oa->type === "docid" || $oa->type === "account" || $oa->type === "thesaurus")) {
                $docTitle = $oa->getOption("doctitle");
                if ($docTitle) {
                    if ($docTitle === "auto") {
                        $orderBy = sprintf("%s_title%s", $reg[1], $reg[2]);
                    } else {
                        $orderBy = sprintf("%s%s", $docTitle, $reg[2]);
                    }
                }
            }
        }

        $resultOrder = \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy($orderBy, $family);
        return $resultOrder;
    }
}