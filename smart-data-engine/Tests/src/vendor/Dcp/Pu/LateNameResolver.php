<?php

namespace Dcp\Pu;

use Anakeen\Core\SEManager;

class LateNameResolver
{
    private $value = null;
    private static $staticEntries = array();

    public function __construct($value)
    {
        $this->value = $value;
        return $this;
    }

    public static function setStaticEntries($entries)
    {
        self::$staticEntries = $entries;
    }

    public function __get($name)
    {
        return $this->resolveNames($this->value);
    }

    private function resolveNames($value)
    {
        if (is_scalar($value)) {
            return $this->resolveNamesScalar($value);
        } else {
            if (is_array($value)) {
                return $this->resolveNamesArray($value);
            }
        }
        return $value;
    }

    private function resolveNamesScalar($value)
    {
        try {
            if (isset(self::$staticEntries[$value])) {
                return self::$staticEntries[$value];
            }
            $id = SEManager::getIdFromName($value);
        } catch (\Exception $e) {
            $id = 0;
        }
        if ($id > 0) {
            return $id;
        }
        return $value;
    }

    private function resolveNamesArray($values)
    {
        array_walk_recursive($values, function (&$item) {
            $item = $this->resolveNamesScalar($item);
        });
        return $values;
    }

    public static function resolve($value)
    {
        return (new self($value))->value;
    }
}
