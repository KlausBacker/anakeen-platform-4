<?php

/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 07/08/18
 * Time: 16:25
 */

class ErrorCodeGRID
{
    /**
     * @errorCode in case of collection not found
     */
    const GRID0001 = 'Error, collection "%s" not found';
    const GRID0002 = 'Error, smart structure "%s" not found';
    const GRID0003 = 'Error, field "%s" not found in "%s" structure';
    const GRID0004 = 'Error, property "%s" is unknown or not displayable';
    const GRID0005 = 'Error, field "%s" cannot be resolved because no collection or smart structure are specified';
    const GRID0006 = 'Error, field or property "%s" cannot be resolved';
    const GRID0007 = 'Cannot load configuration file "%s"';
    const GRID0008 = 'Error, controller name is required';
    const GRID0009 = 'Error, operation id is required';
    const GRID0010 = 'Error, Smart Element Grid controller operation "%s" is unknown';
    const GRID0011 = 'Error, Smart Element Grid controller "%s" does not exist';
    const GRID0012 = 'Error, Smart Element Grid controller "%s" is already registered';
    const GRID0013 = 'Error, class is required';
    const GRID0014 = 'Class "%s" does not implement interface "%s"';
}
