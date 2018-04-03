<?php

namespace Dcp\Pu;

class LateNameResolver
{
    private $value = null;

    public function __construct($value)
    {
        $this->value = $value;
        return $this;
    }

    public function __toString()
    {
        return $this->resolve($this->value);
    }

    private function resolve($value)
    {
        $id = \Anakeen\Core\DocManager::getIdFromName($value);
        if (is_numeric($id)) {
            return (string)$id;
        }
        return (string)$value;
    }
}
