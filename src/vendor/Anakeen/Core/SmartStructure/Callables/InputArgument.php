<?php
namespace Anakeen\Core\SmartStructure\Callables;

class InputArgument
{

    public $name = '';
    public $type = 'any';

    public function __construct($name = '', $type = 'any')
    {
        $this->name = $name;
        $this->type = $type;
    }
}