<?php


namespace Anakeen\SmartCriteria;

class SmartFilter
{

    /**
     * @var string kind
     */
    public $kind;
    /**
     * @var string
     */
    public $field;

    /**
     * @var SmartFilterOperator
     */
    public $operator;
    /**
     * $value
     */
    public $value;
    /**
     * @var string
     */
    public $logic;

    /**
     * SearchFilter constructor.
     * @param string $kind
     * @param string $field
     * @param SmartFilterOperator $operator
     * @param $value
     * @param string $logic
     */
    public function __construct(string $kind, string $field, SmartFilterOperator $operator, $value, string $logic)
    {
        $this->kind = $kind;
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
        $this->logic = $logic;
    }
}
