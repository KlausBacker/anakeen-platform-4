<?php


namespace Anakeen\SmartCriteria;

use Anakeen\Search\Filters\Exception;

class SmartCriteriaConfig
{

    /**
     * @var array
     */
    protected $configuration;

    const KIND_FIELD = "field";
    const KIND_PROPERTY = "property";
    const KIND_VIRTUAL = "virtual";
    const KIND_FULLTEXT = "fulltext";
    const KIND_SUPPORTED_VALUES = [self::KIND_FIELD, self::KIND_PROPERTY, self::KIND_VIRTUAL, self::KIND_FULLTEXT];

    //Default Types
    const TEXTUAL_KIND = "textual";
    const TEMPORAL_KIND = "temporal";
    const NUMERICAL_KIND = "numerical";
    const RELATION_KIND = "relation";
    const FILE_KIND = "file";
    const ENUM_KIND = "enum";
    const STATE_KIND = "state";
    const TITLE_KIND = "title";

    const LOGIC_AND = "and";
    const LOGIC_OR = "or";
    const LOGIC_SUPPORTED_VALUES = [self::LOGIC_AND, self::LOGIC_OR];

    const PROPERTY_TITLE = "title";
    const PROPERTY_STATE = self::STATE_KIND;
    const PROPERTY_SUPPORTED_VALUES = [self::PROPERTY_TITLE, self::PROPERTY_STATE];

    const FIELD = ':field';
    const VALUE = ':value';
    public const SIMPLE_FIELD = "simpleField";
    public const MULTIPLE_FIELD = "multipleField";
    public const SIMPLE_FILTER = "simpleFilter";
    public const MULTIPLE_FILTER = "multipleFilter";

    const BETWEEN_OPERATORS = ["between", "oneBetween"];

    public function __construct($data)
    {
        $this->configuration = $this->getInitialConfiguration();

        if (!is_array($data)) {
            throw new Exception("SMARTCRITERIA0102", print_r($data, true));
        }

        foreach ($data as $config) {
            $type = $config->type;
            $fieldMultiplicity = $config->fieldMultiplicity;
            $filterMultiplicity = $config->filterMultiplicity;
            $key = $config->key;
            $filterValueCallable = $config->filterValueCallable;

            $filterValue = forward_static_call($filterValueCallable);
            if ($filterValue === false) {
                throw new Exception("SMARTCRITERIA0103", $filterValueCallable);
            }

            if (!isset($this->configuration[$type])) {
                $this->configuration[$type] = [
                    self::SIMPLE_FIELD => [
                        self::SIMPLE_FILTER => [],
                        self::MULTIPLE_FILTER => [],
                    ],
                    self::MULTIPLE_FIELD => [
                        self::SIMPLE_FILTER => [],
                        self::MULTIPLE_FILTER => [],
                    ],
                ];
            }

            $fieldMultiple = $fieldMultiplicity === "simple" ? self::SIMPLE_FIELD : self::MULTIPLE_FIELD;
            $filterMultiple = $filterMultiplicity === "simple" ? self::SIMPLE_FILTER : self::MULTIPLE_FILTER;
            $this->configuration[$type][$fieldMultiple][$filterMultiple][$key] = $filterValue;
        }
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    protected function getInitialConfiguration()
    {
        return SmartCriteriaInitialConfiguration::getInitialConfiguration();
    }

    public function getFilterObject(string $type, bool $isFieldMultiple, bool $isFilterMultiple, SmartFilterOperator $operator, string $field = null, $value = null)
    {
        $fieldMultiple = $isFieldMultiple ? self::MULTIPLE_FIELD : SmartCriteriaConfig::SIMPLE_FIELD;
        $filterMultiple = $isFilterMultiple ? self::MULTIPLE_FILTER : SmartCriteriaConfig::SIMPLE_FILTER;
        $criteriaType = SmartCriteriaUtils::getCriteriaKind($type);
        $typeMap = $this->configuration[$criteriaType][$fieldMultiple][$filterMultiple];
        if (!array_key_exists($operator->key, $typeMap)) {
            throw new Exception("FLT0018", $operator->key, $criteriaType, $isFieldMultiple, $isFilterMultiple);
        }
        $operatorData = $typeMap[$operator->key];
        $class = $operatorData["class"];
        $operands = $operatorData["operands"];
        $appliedOptions = $class::getOptionValue($operator->options);
        $operandsValue = array_map(function ($item) use ($appliedOptions, $field, $value) {
            if ($item && substr($item, 0, 1) === ":") {
                $substr = substr($item, 1);
                return $$substr;
            } else {
                return $item + $appliedOptions;
            }
        }, $operands);
        return new $class(...$operandsValue);
    }

    /**
     * Returns the list of operators for a given smartElement type and multiplicity
     * @param $seType
     * @param bool $fieldMultiple
     * @param string $attrName
     * @return array containing the default operators
     */
    public function getDefaultOperators($seType, bool $fieldMultiple, string $attrName = "")
    {
        $kind = SmartCriteriaUtils::getCriteriaKind($seType);
        $multipleField = $fieldMultiple ? self::MULTIPLE_FIELD : self::SIMPLE_FIELD;
        $map = $this->configuration[$kind][$multipleField];
        return array_merge(
            $this->buildDefaultOperatorMap($map, false, $attrName),
            $this->buildDefaultOperatorMap($map, true, $attrName)
        );
    }

    /**
     * Returns the list of operators for a given smartElement type and multiplicity
     * @param $seType
     * @param bool $fieldMultiple
     * @return array containing the default operators
     */
    public function getDefaultOperator($seType, bool $fieldMultiple)
    {
        $kind = SmartCriteriaUtils::getCriteriaKind($seType);
        $multipleField = $fieldMultiple ? self::MULTIPLE_FIELD : self::SIMPLE_FIELD;
        $map = $this->configuration[$kind][$multipleField];
        return $map["defaultOperator"];
    }

    /**
     * @param $map
     * @param bool $isMultiple
     * @param string $attrName
     * @return array
     */
    protected function buildDefaultOperatorMap($map, bool $isMultiple, string $attrName = "")
    {
        $operators = array();
        $multipleFilter = $isMultiple ? self::MULTIPLE_FILTER : self::SIMPLE_FILTER;
        foreach ($map[$multipleFilter] as $operatorName => $operatorValue) {
            $operands = $operatorValue["operands"];

            $acceptValues = false; //in_array does not seem to work
            foreach ($operands as $operand) {
                if ($operand === self::VALUE) {
                    $acceptValues = true;
                    break;
                }
            }
            $isBetween = in_array($operatorName, self::BETWEEN_OPERATORS);
            $class = $operatorValue["class"];
            $hasDynamicLabel = array_key_exists("dynamicLabel", $operatorValue) && $operatorValue["dynamicLabel"];
            foreach ($operatorValue["labels"] as $options => $label) {
                $optionAlias = array();
                $decomposedOptions = $this->decomposeOptions($options);
                if ($options !== 0) {
                    $optionAlias = $class::getOptionAlias($decomposedOptions);
                }

                $processedLabel = $label;
                if ($hasDynamicLabel) {
                    $processedLabel = sprintf($label, $attrName);
                }

                $op = [
                    "key" => $operatorName,
                    "options" => $optionAlias,
                    "label" => $processedLabel,
                    "acceptValues" => $acceptValues,
                    "filterMultiple" => $isMultiple,
                    "isBetween" => $isBetween,
                ];
                array_push($operators, $op);
            }
        }
        return $operators;
    }

    /**
     * Decompose bitmask options
     * @param $options
     * @return array of int options
     */
    protected function decomposeOptions($options)
    {
        $decomposedOptions = array();
        for ($i = 1; $i <= $options; $i = $i * 2) {
            if (($options & $i) > 0) {
                array_push($decomposedOptions, $i);
            }
        }
        return $decomposedOptions;
    }
}
