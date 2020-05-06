<?php

namespace Anakeen\Search\SearchCriteria;

trait SearchCriteriaTrait
{
    protected static $delimiter = ",";


    /**
     * Return the option alias given the inner option.
     * e.g.: option NOT would give "not".
     * @param array $options of int options
     * @return array the option aliases
     */
    public static function getOptionAlias(array $options)
    {
        $map = self::getOptionMap();
        $aliases = array();
        foreach ($options as $option) {
            array_push($aliases, $map[$option]);
        }
        return $aliases;
    }

    /**
     * Return the option value given the option alias.
     * e.g.: option alias "not" would give NOT option value
     * @param array $optionAliases an array of option aliases (string)
     * @return int representing options applied
     */
    public static function getOptionValue(array $optionAliases)
    {
        $flippedMap = array_flip(self::getOptionMap());
        $result = 0;
        foreach ($optionAliases as $alias) {
            $result += $flippedMap[$alias];
        }
        return $result;
    }

    /**
     * @return array
     */
    public static function getOptionMap()
    {
        return array();
    }
}
