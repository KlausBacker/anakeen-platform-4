<?php

namespace Anakeen\Search\Filters\Textual;

class OneEquals extends \Anakeen\Search\Filters\OneEquals
{

    protected $compatibleType = array(
        'text',
        'longtext',
        'htmltext'
    );
}
