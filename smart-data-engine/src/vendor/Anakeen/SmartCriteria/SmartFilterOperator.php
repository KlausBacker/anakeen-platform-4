<?php


namespace Anakeen\SmartCriteria;

class SmartFilterOperator
{

    /**
     * @var string the name of the operator
     */
    public $key;

    /**
     * @var array the options of the operator
     */
    public $options;

    /**
     * @var array the options of the operator
     */
    public $additionalOptions;

    /**
     * @var bool isFilterMultiple
     */
    public $filterMultiple;


    /**
     * SearchFilterOperator constructor.
     * @param string $key
     * @param array $options
     * @param bool $filterMultiple
     * @param array $additionalOptions
     */
    public function __construct(string $key, array $options, bool $filterMultiple, array $additionalOptions = array())
    {
        $this->key = $key;
        $this->options = $options;
        $this->filterMultiple = $filterMultiple;
        $this->additionalOptions = $additionalOptions;
    }
}
