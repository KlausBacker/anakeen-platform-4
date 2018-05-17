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
     * @see      SmartElement::store()
     * @note     hook called before document is created in database
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
    /**
     * call when doc is imported after databases modification
     * the output message will appeared as a information message
     */
    const POSTIMPORT = "postImport";

    /**
     * Call when revise document
     * the output message is an error message it stop revision if is not empty
     * @see \Anakeen\Core\Internal\SmartElement::revise
     */
    const PREREVISE = "preRevise";

    /**
     * Call when revise document
     * the output message will appeared as a information message
     * @see \Anakeen\Core\Internal\SmartElement::revise
     */
    const POSTREVISE = "postRevise";
    /**
     * Call when undelete document
     * the output message is an error message it stop restore if is not empty
     * @see \Anakeen\Core\Internal\SmartElement::undelete()
     */
    const PREUNDELETE = "preUndelete";

    /**
     * Call when undelete document
     * the output message will appeared as a information message
     * @see \Anakeen\Core\Internal\SmartElement::undelete
     */
    const POSTUNDELETE = "postUndelete";
    /**
     * Call when delete document
     * the output message is an error message it stop restore if is not empty
     * @see \Anakeen\Core\Internal\SmartElement::delete()
     */
    const PREDELETE = "preDelete";

    /**
     * Call when delete document
     * the output message will appeared as a information message
     * @see \Anakeen\Core\Internal\SmartElement::delete
     */
    const POSTDELETE = "postDelete";


    /**
     * Call when duplicate document
     * the output message is an error message it stop duplication if is not empty
     * @see \Anakeen\Core\Internal\SmartElement::duplicate()
     */
    const PREDUPLICATE = "preDuplicate";

    /**
     * Call when duplicate document
     * the output message will appeared as a information message
     * @see \Anakeen\Core\Internal\SmartElement::duplicate()
     */
    const POSTDUPLICATE= "postDuplicate";

    public function registerHooks();
}
