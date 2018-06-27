<?php
/*
 * @author Anakeen
 * @package FDL
*/


namespace Anakeen\Routes\Core;

use Anakeen\Router\URLUtils;
use Anakeen\Core\SEManager;
use Anakeen\Core\Settings;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class Enumerates
 *
 * @note    Used by route : POST /api/v2/families/{family}/enums/{enum}
 * @package Anakeen\Routes\Core
 */
class Enumerate
{
    const STARTSOPERATOR = "startswith";
    const CONTAINSOPERATOR = "contains";
    const ORDERBYKEYWORD = "orderBy";
    const ORDERBYKEYOPTION = "key";
    const ORDERBYVALUEOPTION = "label";
    const ORDERBYORDEROPTION = "none";
    /**
     * @var \Anakeen\Core\SmartStructure 
     */
    protected $family = null;
    protected $keywordFilter = '';
    protected $operatorFilter = self::CONTAINSOPERATOR;
    protected $orderBy = self::ORDERBYORDEROPTION;
    protected $enumid = null;
    protected $familyName = null;
    /**
     * @var \Slim\Http\request
     */
    protected $request;


    /**
     * Get Enum Keys and labels
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     * @throws Exception
     * @throws \Dcp\Core\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->request=$request;
        $this->enumid=$args["enum"];
        $this->familyName=$args["family"];

        $this->family = SEManager::getFamily($this->familyName);
        if (!$this->family) {
            $exception = new Exception("CRUD0200", $this->familyName);
            $exception->setHttpStatus("404", "Family not found");
            throw $exception;
        }

        $this->setFilters();

        $attribute = $this->family->getAttribute($this->enumid);
        if (!$attribute) {
            $exception = new Exception("CRUD0400", $this->enumid, $this->family->name);
            $exception->setHttpStatus("404", "Attribute $this->enumid not found");
            throw $exception;
        }
        if ($attribute->type !== "enum") {
            $exception = new Exception("CRUD0401", $this->enumid, $attribute->type, $this->family->name);
            $exception->setHttpStatus("403", "Attribute $this->enumid is not an enum");
            throw $exception;
        }
        /**
         * @var \Anakeen\Core\SmartStructure\NormalAttribute $attribute
         */
        $enums = $attribute->getEnumLabel(null, false);
        $info = array(
            "uri" => $this->generateEnumUrl($this->family->name, $this->enumid),
            "label" => $attribute->getLabel()
        );

        $filterKeyword = $this->getFilterKeyword();
        $filterOperator = $this->getOperatorFilter();
        $pattern = '';
        if ($filterKeyword !== "") {
            switch ($filterOperator) {
                case self::CONTAINSOPERATOR:
                    $pattern = sprintf("/%s/i", str_replace("/", "\\/", preg_quote($filterKeyword)));
                    break;

                case self::STARTSOPERATOR:
                    $pattern = sprintf("/^%s/i", str_replace("/", "\\/", preg_quote($filterKeyword)));
                    break;
            }
        }
        $enumItems = array();
        foreach ($enums as $key => $label) {
            if ($key !== '' && $key !== ' ' && $key !== null) {
                if ($filterKeyword === "" || preg_match($pattern, $label)) {
                    $enumItems[] = array(
                        "key" => (string)$key,
                        "label" => $label
                    );
                }
            }
        }
        switch ($this->getOrderBy()) {
            case self::ORDERBYKEYOPTION:
                usort($enumItems, function ($a, $b) {
                    if ($a['key'] == $b['key']) {
                        return 0;
                    }
                    return ($a['key'] < $b['key']) ? -1 : 1;
                });
                break;

            case self::ORDERBYVALUEOPTION:
                $locale = \Anakeen\Core\ContextManager::getParameterValue('CORE_LANG');
                $collator = new \Collator($locale);

                usort($enumItems, function ($a, $b) use ($collator) {
                    return $collator->compare($a['label'], $b['label']);
                });
                break;
        }
        $info["requestParameters"] = array(
            "operator" => $filterOperator,
            "keyword" => $filterKeyword,
            self::ORDERBYKEYWORD => $this->getOrderBy()
        );
        $info["enumItems"] = $enumItems;

        return ApiV2Response::withData($response, $info);
    }


    /**
     * Prepare query parameters
     */
    protected function setFilters()
    {
        $keywords=$this->request->getQueryParam("keyword");
        if ($keywords) {
            $this->setKeywordFilter($keywords);
        }


        $operator=$this->request->getQueryParam("operator");
        if ($operator) {
            $this->setOperatorFilter($operator);
        }


        $orderBy=$this->request->getQueryParam(self::ORDERBYKEYWORD);
        if ($orderBy) {
            $this->setOrderBy($orderBy);
        }
    }

    /**
     * Register the keyword
     *
     * @param $word
     */
    protected function setKeywordFilter($word)
    {
        if ($word === null) {
            $word = '';
        }
        $this->keywordFilter = $word;
    }

    /**
     * Return the operator filter
     *
     * @return string
     */
    public function getOperatorFilter()
    {
        return $this->operatorFilter;
    }

    /**
     * Set the operator filter
     *
     * @param string $operatorFilter
     *
     * @throws Exception
     */
    public function setOperatorFilter($operatorFilter)
    {
        $availables = array(
            self::STARTSOPERATOR,
            self::CONTAINSOPERATOR
        );
        if (!in_array($operatorFilter, $availables)) {
            throw new Exception("CRUD0402", $operatorFilter, implode(", ", $availables));
        }
        $this->operatorFilter = $operatorFilter;
    }

    /**
     * Return the filter keyword
     *
     * @return string
     */
    protected function getFilterKeyword()
    {
        return $this->keywordFilter;
    }



    protected function generateEnumUrl($famId, $enumId = "")
    {
        if ($enumId !== "") {
            $enumId .= ".json";
        }
        return URLUtils::generateURL(sprintf(
            "%s/families/%s/families/%s",
            Settings::ApiV2,
            $famId,
            $enumId
        ));
    }

    /**
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param string $orderBy
     *
     * @throws Exception
     */
    protected function setOrderBy($orderBy)
    {
        $availables = array(
            self::ORDERBYORDEROPTION,
            self::ORDERBYKEYOPTION,
            self::ORDERBYVALUEOPTION
        );
        if (!in_array($orderBy, $availables)) {
            throw new Exception("CRUD0403", $orderBy, implode(", ", $availables));
        }
        $this->orderBy = $orderBy;
    }
}
