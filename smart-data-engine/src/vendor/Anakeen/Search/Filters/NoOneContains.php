<?php

namespace Anakeen\Search\Filters;

use Anakeen\Search;

class NoOneContains extends OneContains
{
    protected $NOT = true;
}
