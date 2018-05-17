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
    /**
     * call when doc is being imported before any modification
     * if return non null string import will ne aborted
     *
     * @api hook called when import document - before import it
     *
     * @param array $extra extra parameters
     *
     */
    const PREIMPORT = "preImport";
    const POSTIMPORT = "postImport";

    public function registerHooks();
}
