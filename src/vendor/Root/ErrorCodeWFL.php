<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used to checking workflows
 * @class ErrorCodeWFL
 * @see ErrorCode
 * @brief List all error code for workflows class definition
 */
class ErrorCodeWFL
{
    /**
     * @errorCode
     * transition declaration must be an array
     */
    const WFL0200 = 'workflow transition is not an array for class %s';
    /**
     * @errorCode
     * transition declaration must be an array
     */
    const WFL0201 = 'workflow transition unknow property %s for transition #%d in class %s (must be one of %s)';
    /**
     * @errorCode
     * the model transition is required to declare a transition
     */
    const WFL0202 = 'workflow transition #%d property \'t\' is mandatory in class %s';
    /**
     * @errorCode
     * The element of the $cycle must be an array()
     */
    const WFL0203 = "workflow transition element at index %d must be an array in class %s (found %s instead)";
    /**
     * @errorCode
     * transition are declared in a an array
     * @code
     * public $transitions = array(
     self::T1 => array(
     "m1" => "SendMailToVeto",
     "ask" => array(
     "wan_idveto",
     "wan_veto"
     ) ,
     "nr" => true
     ) ,
     * @endcode
     */
    const WFL0100 = 'workflow transition model is not an array for class %s';
    /**
     * @errorCode
     * field use for transition declaration must be valid
     */
    const WFL0101 = 'workflow transition unknow property %s for transition model %s in class %s (must be one of %s)';
    /**
     * @errorCode
     * number of transition model and states are limited
     * Considers that you have 1500 points to determine transition and states.
     * a transition cost 4 points and a state 12 points.
     * generally you could have around 200 transitions for 60 states
     */
    const WFL0102 = 'workflow %s number of transition model and states (found %d) exceed limit (max is %s)';
    /**
     * @errorCode
     * declaration of ask must be in an array
     * @code
     * public $transitions = array(
     self::T1 => array(
     "ask" => array(
     "wan_idveto",
     "wan_veto"
     )
     ) ,
     * @endcode
     */
    const WFL0103 = 'workflow transition ask is not an array for transition model %s in class %s';
    /**
     * @errorCode
     * ask array must reference workflow attributes
     */
    const WFL0104 = 'unknow attribute %s in workflow transition ask in class %s';
    /**
     * @errorCode
     * m1 property must be a worflow method
     */
    const WFL0105 = 'workflow unknow m1 method %s for transition model %s in class %s';
    /**
     * @errorCode
     * m2 property must be a worflow method
     */
    const WFL0106 = 'workflow unknow m2 method %s for transition model %s in class %s';
    /**
     * @errorCode
     * nr property is a boolean
     */
    const WFL0107 = 'workflow transition nr property is not a boolean for transition model %s in class %s';
    /**
     * @errorCode
     * m0 property must be a worflow method
     */
    const WFL0108 = 'workflow unknow m0 method %s for transition model %s in class %s';
    /**
     * @errorCode
     * m3 property must be a worflow method
     */
    const WFL0109 = 'workflow unknow m3 method %s for transition model %s in class %s';
    /**
     * @errorCode
     * The transition element must have a key and an array() value
     */
    const WFL0110 = "transition element with key '%s' (index %d) must be an array in class %s (found %s instead)";
    /**
     * @errorCode
     *
     */
    const WFL0050 = 'workflow transition or state key %s syntax error for %s (limit to %d alpha characters)';
    /**
     * @errorCode
     * if family is declared as workflow, classname field is required
     */
    const WFL0001 = 'workflow class name is empty';
    /**
     * @errorCode
     * the name of a workflow class must be a valid PHP name class
     */
    const WFL0002 = 'class name %s not valid';
    /**
     * @errorCode
     * PHP file is not valid
     */
    const WFL0003 = 'PHP parsing %s';
    /**
     * @errorCode
     * cannot find a class named as it is needed by workflow
     */
    const WFL0004 = 'workflow class %s not found';
    /**
     * @errorCode
     * the file of the workflow PHP class is not found
     */
    const WFL0005 = 'file name "%s" for %s not found';
    /**
     * @errorCode
     * the workflow class must be a descendant of WDoc class
     */
    const WFL0006 = 'workflow class %s not inherit from WDoc class';
    /**
     * @errorCode
     * the attrPrefix must not be empty
     */
    const WFL0007 = 'workflow : missing attrPrefix definition for %s class';
    /**
     * @errorCode
     * the attrPrefix must be composed of just few characters
     */
    const WFL0008 = 'workflow : syntax error attrPrefix for %s class (limit to 15 alpha characters)';
    /**
     * @errorCode
     * activies label is not an array
     */
    const WFL0051 = 'workflow activies labels is not an array for class %s';
    /**
     * @errorCode
     * activies label not match any state
     */
    const WFL0052 = 'unknow state %s for the activity %s for class %s';
}
