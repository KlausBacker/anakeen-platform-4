<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Dcp\DocumentGrid2;
/**
 * SearchCriteria : basic class to create a simple criteria langage
 */
class SearchCriteria
{
    /**
     * Def of all possible criterias
     *
     * @var array
     */
    public static $criteriasDef = array(
        "dc:na" => array(
            "type" => "all",
            "preInsert" => array(
                "notNeeded"
            ) ,
            "simple" => '',
            "multiple" => ''
        ) ,
        "dc:empty" => array(
            "type" => "all",
            "simple" => '%1$s is NULL',
            "multiple" => 'replace(%1$s, \'<BR>\', E\'\\n\') ~ E\'^\\\\n+$\' or %1$s is NULL'
        ) ,
        "dc:not empty" => array(
            "type" => "all",
            "simple" => '%1$s is not NULL',
            "multiple" => 'replace(%1$s, \'<BR>\', E\'\\n\') !~ E\'^\\\\n+$\''
        ) ,
        "dc:equal" => array(
            "type" => array(
                "int",
                "double",
                "money",
                "date",
                "timestamp",
                "state",
                "enum",
                "docid",
                "account"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "simple" => '%1$s = \'%2$s\'',
            "multiple" => 'replace(%1$s, \'<BR>\', E\'\\n\') = \'%2$s\''
        ) ,
        "dc:not equal" => array(
            "type" => array(
                "int",
                "double",
                "money",
                "date",
                "timestamp",
                "state",
                "enum",
                "docid",
                "account"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "simple" => '%1$s <> \'%2$s\' or %1$s is NULL',
            "multiple" => 'replace(%1$s, \'<BR>\', E\'\\n\') <> \'%2$s\' or %1$s is NULL'
        ) ,
        "dc:inferior" => array(
            "type" => array(
                "int",
                "double",
                "money",
                "date",
                "timestamp"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "simple" => '%1$s < \'%2$s\''
        ) ,
        "dc:superior" => array(
            "type" => array(
                "int",
                "double",
                "money",
                "date",
                "timestamp"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "simple" => '%1$s > \'%2$s\''
        ) ,
        "dc:between" => array(
            "type" => array(
                "int",
                "double",
                "money",
                "date",
                "timestamp"
            ) ,
            "preInsert" => array(
                "testIfEmpty",
                "testTwoValues"
            ) ,
            "simple" => '%1$s >= \'%2$s\' and %1$s <= \'%3$s\''
        ) ,
        "dc:contain" => array(
            "type" => array(
                "text",
                "htmltext",
                "longtext"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "simple" => '%1$s ~* \'%2$s\''
        ) ,
        "dc:not contain" => array(
            "type" => array(
                "text",
                "htmltext",
                "longtext"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "simple" => '%1$s !~* \'%2$s\' or %1$s is NULL'
        ) ,
        // ---------- thesaurus criteria part ---------
        "dc:th strict equal" => array(
            "type" => array(
                "thesaurus"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "simple" => '%1$s = \'%2$s\'',
            "multiple" => 'replace(%1$s, \'<BR>\', E\'\\n\') = \'%2$s\''
        ) ,
        "dc:th equal" => array(
            "type" => array(
                "thesaurus"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "simple" => '\\Dcp\\DocumentGrid2\\ThesaurusCriteria::getThEqual()'
        ) ,
        "dc:th not equal" => array(
            "type" => array(
                "thesaurus"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "simple" => '\\Dcp\\DocumentGrid2\\ThesaurusCriteria::getThNotEqual()'
        ) ,
        "dc:th one equal" => array(
            "type" => array(
                "thesaurus"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "multiple" => '\\Dcp\\DocumentGrid2\\ThesaurusCriteria::getThEqual()'
        ) ,
        "dc:th no one equal" => array(
            "type" => array(
                "thesaurus"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "multiple" => '\\Dcp\\DocumentGrid2\\ThesaurusCriteria::getThNotEqual()'
        ) ,
        "dc:th not strict equal" => array(
            "type" => array(
                "thesaurus"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "simple" => '%1$s <> \'%2$s\' or %1$s is NULL',
            "multiple" => 'replace(%1$s, \'<BR>\', E\'\\n\') <> \'%2$s\' or %1$s is NULL'
        ) ,
        "dc:th without" => array(
            "type" => array(
                "thesaurus"
            ) ,
            "preInsert" => array(
                "testIfEmpty",
                "fusionFilterAndArrayOfValue"
            ) ,
            "multiple" => 'not(ARRAY[%2$s] && (regexp_split_to_array(replace(%1$s, \'<BR>\', E\'\\n\'), E\'\\n\' ))) or %1$s is NULL'
        ) ,
        "dc:th with" => array(
            "type" => array(
                "thesaurus"
            ) ,
            "preInsert" => array(
                "testIfEmpty",
                "fusionFilterAndArrayOfValue"
            ) ,
            "multiple" => 'ARRAY[%2$s] && (regexp_split_to_array(replace(%1$s, \'<BR>\', E\'\\n\'), E\'\\n\' ))'
        ) ,
        // ---------- multiple criteria part ---------
        "dc:one contain" => array(
            "type" => array(
                "text",
                "htmltext",
                "longtext"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "multiple" => '%1$s ~* \'%2$s\''
        ) ,
        "dc:no one contain" => array(
            "type" => array(
                "text",
                "htmltext",
                "longtext"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "multiple" => '%1$s !~* \'%2$s\' or %1$s is NULL'
        ) ,
        "dc:one equal" => array(
            "type" => array(
                "int",
                "double",
                "money",
                "date",
                "timestamp"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "multiple" => '\'%2$s\' = ANY (regexp_split_to_array(replace(%1$s, \'<BR>\', E\'\\n\'), E\'\\n\' ))'
        ) ,
        "dc:no one equal" => array(
            "type" => array(
                "int",
                "double",
                "money",
                "date",
                "timestamp"
            ) ,
            "preInsert" => array(
                "testIfEmpty"
            ) ,
            "multiple" => '\'%2$s\' <> ALL (regexp_split_to_array(replace(%1$s, \'<BR>\', E\'\\n\'), E\'\\n\' )) or %1$s is NULL'
        ) ,
        "dc:with" => array(
            "type" => array(
                "state",
                "enum",
                "docid",
                "account"
            ) ,
            "preInsert" => array(
                "testIfEmpty",
                "fusionFilterAndArrayOfValue"
            ) ,
            "multiple" => 'ARRAY[%2$s] && (regexp_split_to_array(replace(%1$s, \'<BR>\', E\'\\n\'), E\'\\n\' ))'
        ) ,
        "dc:without" => array(
            "type" => array(
                "state",
                "enum",
                "docid",
                "account"
            ) ,
            "preInsert" => array(
                "testIfEmpty",
                "fusionFilterAndArrayOfValue"
            ) ,
            "multiple" => 'not(ARRAY[%2$s] && (regexp_split_to_array(replace(%1$s, \'<BR>\', E\'\\n\'), E\'\\n\' ))) or %1$s is NULL'
        )
    );
    /**
     * @var \SearchDoc
     */
    protected $currentSearchDoc = null;
    /**
     * PreRender notNeeded
     * @static
     * @return bool => false
     */
    static protected function notNeeded()
    {
        return false;
    }
    /**
     * Don't create criteria for empty value
     *
     * @static
     * @param $value
     * @return bool
     */
    static protected function testIfEmpty($value)
    {

        if (isset($value["key"])) {
            $value=$value["key"];
        }
        if (is_numeric($value)) {
            return true;
        }
        return !empty($value) ;
    }
    /**
     * Test if min and max values are present and format it for the filter
     *
     * @static
     * @param array $value
     * @return bool
     */
    static protected function testTwoValues(Array & $value)
    {
        if (isset($value["valueMin"]) && isset($value["valueMax"]) && (!empty($value["valueMin"]) || is_numeric($value)) && (!empty($value["valueMax"]) || is_numeric($value))) {
            $value = array(
                $value["valueMin"],
                $value["valueMax"]
            );
            return true;
        }
        return false;
    }
    /**
     * Push all the values in the filter to handle the addFilter pg_escape_string
     *
     * @static
     * @param array $value
     * @param $filter
     * @param $id
     * @return bool
     */
    static protected function fusionFilterAndArrayOfValue(&$value, &$filter, $id)
    {
        if (is_string($value)) {
            $value = array(
                $value
            );
        }
        if (!is_array($value)) {
            return false;
        }
        $value = join(",", array_map(function ($currentValue)
        {
            if (isset($currentValue["key"])) {
                $currentValue=$currentValue["key"];
            }
            $currentValue = pg_escape_string($currentValue);
            return "'$currentValue'";
        }
        , $value));
        $filter = sprintf($filter, $id, $value);
        $value = "";
        return true;
    }
    /**
     * Used to call a searchDoc filter with a unknown numbers of parameters
     *
     * @var \ReflectionMethod
     */
    protected $searchDocFilter = null;
    /**
     * Create the element
     *
     * @param \SearchDoc $searchDoc
     */
    public function __construct(\SearchDoc $searchDoc)
    {
        $this->currentSearchDoc = $searchDoc;
        $this->searchDocFilter = new \ReflectionMethod('SearchDoc', 'addFilter');
    }
    /**
     * Add an array of criterias
     *
     * @param array $criteriasDefinition eachLines must have an id, operator, type and multiplicity
     */
    public function addCriterias(Array $criteriasDefinition)
    {
        foreach ($criteriasDefinition as $currentCriteria) {
            $value = isset($currentCriteria["value"])?$currentCriteria["value"]:"";
            $this->addCriteria(new CriteriaStruct($currentCriteria["id"], $currentCriteria["operator"], $currentCriteria["type"], $value, $currentCriteria["multiplicity"]));
        }
    }
    /**
     * Add a criteria
     *
     * @deprecated use SearchCriteria::addCriteria instead
     * @param string $id : attrId or property
     * @param string $operator : on of the criteriasDef defined operators
     * @param string $value : value or array of values
     * @param string $type : kind of attrId or property
     * @param string $multiplicity : "simple" or "multiple"
     * @return bool
     * @throws UnknownCriteriaException
     */
    public function addACriteria($id, $operator, $value, $type, $multiplicity = "simple")
    {
        $this->addCriteria(new CriteriaStruct($id, $operator, $type, $value, $multiplicity));
    }
    /**
     * Add a criteria
     *
     * @param CriteriaStruct $criteria the criteria to add
     * @throws SearchCriteriaException
     * @throws UnknownCriteriaException
     * @return bool
     */
    public function addCriteria(CriteriaStruct $criteria)
    {
        if (isset(self::$criteriasDef[$criteria->operator]) && isset(self::$criteriasDef[$criteria->operator][$criteria->multiplicity]) && (self::$criteriasDef[$criteria->operator]["type"] === "all" || in_array($criteria->type, self::$criteriasDef[$criteria->operator]["type"]))) {
            $filter = self::$criteriasDef[$criteria->operator][$criteria->multiplicity];
            if (isset(self::$criteriasDef[$criteria->operator]["preInsert"])) {
                foreach (self::$criteriasDef[$criteria->operator]["preInsert"] as $currentTest) {
                    if (!self::$currentTest($criteria->value, $filter, $criteria->id)) {
                        return true;
                    }
                }
            }
            
            if (preg_match('/^[a-z]*::[a-z]+\(/i', $filter, $reg)) {
                // use a method to render sql filter
                $parseMethod = new \parseFamilyMethod();
                $parseMethod->parse($filter);
                $err = $parseMethod->getError();
                if ($err) {
                    throw new SearchCriteriaException(sprintf('Filter error (criteria : %s', $criteria));
                }
                $staticClass = $parseMethod->className;
                if (!$staticClass) {
                    $staticClass = $this;
                }
                $methodName = $parseMethod->methodName;
                if (!method_exists($staticClass, $methodName)) {
                    throw new SearchCriteriaException(sprintf('Filter method "%s" not found (criteria : %s)', $methodName, $criteria));
                }
                
                $args = array(
                    $criteria
                );
                try {
                    $filter = call_user_func_array(array(
                        $staticClass,
                        $methodName,
                    ) , $args);
                }
                catch(\Exception $e) {
                    throw new SearchCriteriaException(sprintf('Filter method "%s" error "%s" (criteria : %s', $methodName, $e->getMessage() , $criteria));
                }
            }
            
            if (is_array($criteria->value)) {
                array_unshift($criteria->value, $filter, $criteria->id);
                $this->searchDocFilter->invokeArgs($this->currentSearchDoc, $criteria->value);
            } else {
                $this->currentSearchDoc->addFilter($filter, $criteria->id, $criteria->value);
            }
            return true;
        }
        throw new UnknownCriteriaException(sprintf('Unknown criteria : %s', $criteria));
    }
    /**
     * Return the current SearchDoc
     *
     * @return \SearchDoc
     */
    public function getSearchDoc()
    {
        return $this->currentSearchDoc;
    }
}

class UnknownCriteriaException extends \Exception
{
}
class SearchCriteriaException extends \Exception
{
}

class CriteriaStruct
{
    public function __construct($id, $operator, $type = "text", $value = "", $multiplicity = "simple")
    {
        $this->id = $id;
        $this->operator = $operator;
        $this->type = $type;
        $this->value = $value;
        $this->multiplicity = $multiplicity;
    }
    public function __toString()
    {
        return sprintf('operator : "%s", id : "%s", value : "%s", type : "%s", multiplicity : "%s"', $this->operator, $this->id, $this->value, $this->type, $this->multiplicity);
    }
    /** @var string attribute / property identifier so column identifier*/
    public $id;
    /** @var string one of SearchCriteria::criteriaDef */
    public $operator;
    /** @var  string optional value for operator */
    public $value;
    /** @var  string type of identifier */
    public $type;
    /** @var  string if identifier is multiple : "simple" or "multiple"  */
    public $multiplicity = "simple";
}
