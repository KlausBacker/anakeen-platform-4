<?php

namespace Anakeen\Search\Filters\Textual;

class Equals extends \Anakeen\Search\Filters\IsEqual
{

    protected $compatibleType = array(
        'text',
        'longtext',
        'htmltext'
    );
}
