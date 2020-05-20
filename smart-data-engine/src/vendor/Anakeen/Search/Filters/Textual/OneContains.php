<?php

namespace Anakeen\Search\Filters\Textual;

class OneContains extends \Anakeen\Search\Filters\OneContains
{

    protected $compatibleType = array(
        'text',
        'longtext',
        'htmltext'
    );
}
