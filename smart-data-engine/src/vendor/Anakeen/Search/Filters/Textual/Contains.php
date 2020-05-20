<?php

namespace Anakeen\Search\Filters\Textual;

class Contains extends \Anakeen\Search\Filters\Contains
{

    protected $compatibleType = array(
        'text',
        'longtext',
        'htmltext'
    );
}
