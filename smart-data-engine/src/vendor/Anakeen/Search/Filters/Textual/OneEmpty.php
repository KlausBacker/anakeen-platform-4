<?php

namespace Anakeen\Search\Filters\Textual;

class OneEmpty extends \Anakeen\Search\Filters\OneEmpty
{

    protected $compatibleType = array(
        'text',
        'longtext',
        'htmltext'
    );
}
