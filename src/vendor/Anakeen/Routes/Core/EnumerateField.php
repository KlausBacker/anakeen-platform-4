<?php


namespace Anakeen\Routes\Core;

use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;

/**
 * Class Enumerates
 *
 * @note    Used by route : POST /api/v2/families/{family}/enumerates/{enum}
 * @package Anakeen\Routes\Core
 */
class EnumerateField extends Enumerate
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
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->request = $request;
        $this->enumid = $args["enum"];
        $this->familyName = $args["family"];

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
        $args["enum"] = $attribute->format;
        return parent::__invoke($request, $response, $args);
    }


}
