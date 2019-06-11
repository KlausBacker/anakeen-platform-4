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
     * @note     hook called before smart element is created in database
     * @return string error message
     */
    const PRECREATED = "preCreated";
    /**
     * call when doc is being imported before any modification
     * if return non null string import will ne aborted
     *
     * @api hook called when import smart element - before import it
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
     * Call when revise smart element
     * the output message is an error message it stop revision if is not empty
     * @see \Anakeen\Core\Internal\SmartElement::revise
     */
    const PREREVISE = "preRevise";

    /**
     * Call when revise smart element
     * the output message will appeared as a information message
     * @see \Anakeen\Core\Internal\SmartElement::revise
     */
    const POSTREVISE = "postRevise";
    /**
     * Call when undelete smart element
     * the output message is an error message it stop restore if is not empty
     * @see \Anakeen\Core\Internal\SmartElement::undelete()
     */
    const PREUNDELETE = "preUndelete";

    /**
     * Call when undelete smart element
     * the output message will appeared as a information message
     * @see \Anakeen\Core\Internal\SmartElement::undelete
     */
    const POSTUNDELETE = "postUndelete";
    /**
     * Call when delete smart element
     * the output message is an error message it stop restore if is not empty
     * @see \Anakeen\Core\Internal\SmartElement::delete()
     */
    const PREDELETE = "preDelete";

    /**
     * Call when delete smart element
     * the output message will appeared as a information message
     * @see \Anakeen\Core\Internal\SmartElement::delete
     */
    const POSTDELETE = "postDelete";


    /**
     * Call when duplicate smart element
     * the output message is an error message it stop duplication if is not empty
     * @see \Anakeen\Core\Internal\SmartElement::duplicate()
     */
    const PREDUPLICATE = "preDuplicate";

    /**
     * Call when duplicate smart element
     * the output message will appeared as a information message
     * @see \Anakeen\Core\Internal\SmartElement::duplicate()
     */
    const POSTDUPLICATE = "postDuplicate";


    /**
     * Call when affect smart element object with new data
     * The object not has new data yet
     * @see \Anakeen\Core\Internal\SmartElement::affect()
     */
    const PREAFFECT = "preAffect";

    /**
     * Call when affect smart element with new data
     * The data is set in object
     * @see \Anakeen\Core\Internal\SmartElement::affect()
     */
    const POSTAFFECT = "postAffect";


    /**
     * Call when use refresh method
     * Call at store before computed fields are set
     * @see \Anakeen\Core\Internal\SmartElement::refresh()
     */
    const PREREFRESH = "preRefresh";
    /**
     * Call when use refresh method
     * Call at store after computed fields are set
     * @see \Anakeen\Core\Internal\SmartElement::refresh()
     */
    const POSTREFRESH = "postRefresh";

    public function registerHooks();
}
