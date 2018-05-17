<?php

namespace Anakeen;

use Anakeen\Core\Internal\SmartElement;

interface SmartHooks
{
    const POSTSTORE = "postStore";
    const PRESTORE = "preStore";
    const POSTCREATED = "postCreated";
    /**
     * call in SmartElement::Add() method
     * if return message, creation is aborted
     * @see SmartElement::store()
     * @notehook called before document is created in database
     * @return string error message
     */
    const PRECREATED = "preCreated";

    public function registerHooks();
}
