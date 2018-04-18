<?php
/*
 * @author Anakeen
 * @package FDL
 */
namespace Anakeen\Core\Internal;
class StoreInfo
{
    const NO_ERROR = 0;
    /**
     * preCreated has return an error
     */
    const CREATE_ERROR = 2;
    /**
     * database record has return error
     */
    const UPDATE_ERROR = 3;
    /**
     * all constraints are not validated
     */
    const CONSTRAINT_ERROR = 4;
    /**
     * preStore has returned an error
     */
    const PRESTORE_ERROR = 5;
    /**
     * @var string message returned by \Anakeen\Core\Internal\SmartElement::refresh
     */
    public $refresh = '';
    /**
     * @var string message returned by \Anakeen\Core\Internal\SmartElement::postStore
     */
    public $postStore = '';

    /**
     * @var string message returned by \Anakeen\Core\Internal\SmartElement::preStore
     */
    public $preStore = '';
    /**
     * set of information about constraint test indexed by attribute identifier and rank index if multiple attribute
     * @var array message returned by \Anakeen\Core\Internal\SmartElement::verifyAllConstraints
     */
    public $constraint = array();
    /**
     * @var string store error, empty if no errors
     */
    public $error = '';
    /**
     * @var int error code
     */
    public $errorCode = 0;
}
