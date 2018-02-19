<?php

namespace Anakeen\Router;

class ParameterInfo
{
    public $value;
    public $description;
    public $access;
    public $type = "text";
    public $category;
    public $isUser=false;
    public $global = true;
}
