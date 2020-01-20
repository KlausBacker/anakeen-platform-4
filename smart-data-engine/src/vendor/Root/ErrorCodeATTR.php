<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * Error codes used to checking family Smart Field structure
 * @class ErrorCodeATTR
 * @see   ErrorCode
 * @brief List all error code for document's Smart Field errors
 * It is triggered by 'ATTR', 'PARAM', 'MODATTR' keywords
 */
class ErrorCodeATTR
{
    /**
     * @errorCode  Smart Field identicator is limit to 63 alphanum characters
     */
    const ATTR0100 = 'syntax error for Smart Field "%s"';
    /**
     * @errorCode  Smart Field identifier cannot be equal to a reserved postgresql word
     */
    const ATTR0101 = 'Smart Field identifier "%s" use a reserved word';
    /**
     * @errorCode  Smart Field identifier is required
     */
    const ATTR0102 = 'Smart Field identifier is not set';
    /**
     * @errorCode  Smart Field identifier cannot be equal to a doc properties name
     */
    const ATTR0103 = 'Smart Field identifier "%s" use a property identificator';
    /**
     * @errorCode  UPDTATTR : Use only if Smart Field is recorded before
     */
    const ATTR0104 = 'Cannot update unknow Smart Field identifier "%s" ';
    /**
     * @errorCode  Smart Field identicator is limit to 63 alphanum characters
     */
    const ATTR0200 = 'syntax error for structure "%s" Smart Field "%s"';
    /**
     * @errorCode  Smart Field structure identifier is required
     */
    const ATTR0201 = 'Smart Field structure is not set for Smart Field "%s"';
    /**
     * @errorCode  Smart Field structure must reference other Smart Field
     */
    const ATTR0202 = 'Smart Field structure is same as Smart Field "%s"';
    /**
     * @errorCode  Smart Field structure must reference an Smart Field
     */
    const ATTR0203 = 'Smart Field structure reference is not an Smart Field for "%s", family "%s"';
    /**
     * @errorCode  Smart Field structure must compatible with level hierarchy
     * @note       a tab has no parent,
     * a frame must have only tab parent,
     * another Smart Field must have only frame parent or array parent
     */
    const ATTR0204 = 'Smart Field structure "%s" is not possible for "%s"';
    /**
     * @errorCode  Smart Field structure must reference a frame or array Smart Field for normal Smart Field
     */
    const ATTR0205 = 'this Smart Field structure "%s" is not a allowed for normal Smart Field"%s"';
    /**
     * @errorCode  Smart Field structure must be empty for tab Smart Field
     */
    const ATTR0206 = 'Smart Field structure "%s" is forbidden for a tab  Smart Field for "%s"';
    /**
     * @errorCode  Smart Field structure must reference a tab Smart Field or nothing
     */
    const ATTR0207 = 'this Smart Field structure "%s" is not a allowed for frame Smart Field "%s"';
    /**
     * @errorCode  Parameter structure must reference a parameter
     */
    const ATTR0208 = 'param structure reference is not a parameter for "%s", family "%s"';
    /**
     * @errorCode  Parameter structure must compatible with level hierarchy
     * @note       a tab has no parent,
     * a frame must have only tab parent,
     * another parameter must have only frame parent or array parent
     */
    const ATTR0209 = 'parameter structure "%s" is not possible for "%s"';
    /**
     * @errorCode  Parameter structure must reference a frame or array parameter for parameter
     */
    const ATTR0210 = 'this parameter structure "%s" is not a allowed for parameter"%s"';
    /**
     * @errorCode
     */
    const ATTR0211 = 'parameter "%s" must not have a phpfunc with output variables';
    /**
     * @errorCode  Smart Field order must reference an Smart Field family
     */
    const ATTR0212 = 'the Smart Field "%s" has incorrect order  : "%s" (must be numeric or reference an Smart Field)';
    /**
     * @errorCode  Smart Field order reference is not in the current frame
     */
    const ATTR0213 = 'the Smart Field "%s" has incorrect order  : parent is "%s" must be "%s": out of field';

    /**
     * @errorCode A parent id reference a child id
     */
    const ATTR0214 = 'the Smart Field "%s" has a loop reference for parent ';
    /**
     * @errorCode  Smart Field isTitle is Y or N
     */
    const ATTR0400 = 'invalid value "%s" for isTitle in Smart Field "%s"';
    /**
     * @errorCode  Smart Field isTitle must not be Y for structured Smart Fields
     */
    const ATTR0401 = 'isTitle cannot be set for structured Smart Field "%s"';
    /**
     * @errorCode  Smart Field isAbstract is Y or N
     */
    const ATTR0500 = 'invalid value "%s" for isAbstract in Smart Field "%s"';
    /**
     * @errorCode  Smart Field isAbstract must not be Y for structured Smart Fields
     */
    const ATTR0501 = 'isAbstract cannot be set for structured Smart Field "%s"';
    /**
     * @errorCode  Smart Field type is required
     */
    const ATTR0600 = 'type is not defined for Smart Field "%s"';
    /**
     * @errorCode  Smart Field type is not available
     */
    const ATTR0601 = 'unrecognized Smart Field type "%s" (Smart Field "%s"), type is one of %s';
    /**
     * @errorCode  a type is can be only a alpha characters
     * example text, double, money("%.02f $")
     */
    const ATTR0602 = 'syntax error for type "%s" in Smart Field "%s"';
    /**
     * @errorCode  the format string must contains only one %s variable
     */
    const ATTR0603 = 'bad output format "%s" in Smart Field "%s" ';
    /**
     * @errorCode the basic type set in a MODATTR cannot be changed
     */
    const ATTR0604 = 'incompatible redefinition of type for "%s" Smart Field (family "%s"). New type "%s" is not compatible with origin "%s"';
    /**
     * @errorCode a MODATTR Smart Field is not defined in its ancestor
     * Cannot modify an Smart Field which has not defined before
     */
    const ATTR0605 = 'Smart Field modification for "%s" Smart Field (family "%s") is not found in ancestor';
    /**
     * @errorCode a enum MODATTR Smart Field cannot redefine its items
     * If need redefine, a ATTR must be used (not a MODATTR) to redefine the attribut and cut inheritance
     */
    const ATTR0606 = 'enum Smart Field modification for "%s" Smart Field (family "%s"): the enum items cannot be redefined';
    /**
     * @errorCode The Smart Field's order must be a number or an Smart Field id reference
     */
    const ATTR0700 = 'the order "%s" must be a number or an Smart Field reference in Smart Field "%s"';
    /**
     * @errorCode  The Smart Field's order is required when  Smart Field is not a frame or a tab
     */
    const ATTR0702 = 'the order is required in Smart Field "%s"';
    /**
     * @errorCode  The Smart Field's access must be defined
     */
    const ATTR0800 = 'the access is required in field "%s"';
    /**
     * @errorCode  The Smart Field's access is limited to defined visibilities (H,R,...)
     */
    const ATTR0801 = 'the access "%s" in field "%s" must be one of %s';
    /**
     * @errorCode  The U visibility can be applied only on array Smart Field
     */
    const ATTR0802 = 'the U visibility is reserved to array, in Smart Field "%s"';
    /**
     * @errorCode  The Smart Field's access is limited to defined visibilities (Read, Write)
     */
    const ATTR0803 = 'the access "%s" in field is incorrect';
    /**
     * @errorCode  property isNeeded is Y or N
     */
    const ATTR0900 = 'invalid value "%s" for isNeeded in Smart Field "%s"';
    /**
     * @errorCode  property isNeeded must not be Y for structured Smart Fields
     */
    const ATTR0901 = 'isNeeded cannot be set for structured Smart Field "%s"';
    /**
     * @errorCode  property isNeeded cannot be used when Smart Field is included in an array
     */
    const ATTR0902 = 'isNeeded cannot be set for  Smart Field included in array "%s"';
    /**
     * @errorCode  property isNeeded cannot be used when parameter is included in an array
     */
    const ATTR0903 = 'isNeeded cannot be set for parameter included in array "%s"';
    /**
     * @errorCode  syntaxt error in method describe in link
     */
    const ATTR1000 = 'method syntax error in link "%s" for Smart Field "%s":%s';
    /**
     * @errorCode  call method in link property must be defined in file method or in another class if precised
     * @see        ATTR1260
     * @see        ATTR1261
     * @see        ATTR1262
     * @see        ATTR1263
     */
    const ATTR1001 = 'link method error in "%s" family : %s';
    /**
     * @errorCode  Method use in link must have @apiExpose tag comment in their description
     */
    const ATTR1002 = 'method "%s" in Smart Field link "%s" is not an exposable method';
    /**
     * @errorCode  The input help file must exists before declared it
     */
    const ATTR1100 = 'the input help file "%s" not exists, in Smart Field "%s"';
    /**
     * @errorCode  The input help file must be a correct PHP file
     */
    const ATTR1101 = 'the input help file "%s" is not parsable, in Smart Field "%s" : %s';
    /**
     * @errorCode  The autocomplete response results indexes must be compatioble with description
     */
    const ATTR1102 = 'the autocomplete send less data as specified, missing index "%s" for field "%s"';
    /**
     * @errorCode  The option name are composed only of alpha characters
     */
    const ATTR1500 = 'the option name "%s" is not valid in Smart Field "%s"';
    /**
     * @errorCode  The syntax option is : optname=optvalue
     * @note       example : elabel=enter a value
     */
    const ATTR1501 = 'the option "%s" must have = sign, in Smart Field "%s"';
    /**
     * @errorCode  the phpfunc must be a call to a valid function or method
     */
    const ATTR1200 = 'syntax error in phpfunc Smart Field  "%s" : %s';
    /**
     * @errorCode  function must have 2 parenthesis one open and one close
     */
    const ATTR1201 = 'error parenthesis in method/file definition : "%s"';
    /**
     * @errorCode  function name must be a valid PHP name
     */
    const ATTR1202 = 'syntax error in function name : "%s"';
    /**
     * @errorCode  function name must exists
     */
    const ATTR1203 = 'function "%s" not exists';
    /**
     * @errorCode  double quote error in function call
     */
    const ATTR1204 = 'double quote syntax error (character %d) in function "%s"';
    /**
     * @errorCode  simple quote error in function call
     */
    const ATTR1205 = 'simple quote syntax error (character %d) in function "%s"';
    /**
     * @errorCode  output Smart Fields must be declared after semicolumn characters
     * @note
     * example : test():MY_TEST1, MY_TEST2
     */
    const ATTR1206 = 'no output Smart Field, missing ":" character in function "%s"';
    /**
     * @errorCode  output Smart Fields must represent Smart Field name with a comma separator
     * @note
     *  example :test():MY_TEST1, MY_TEST2
     *  test(My_TEST2):MY_TEST1
     */
    const ATTR1207 = 'outputs in function "%s" can be only alphanum characters ';
    /**
     * @errorCode  appname must be only alphanum characters
     * @note       when use special help as help input
     */
    const ATTR1208 = 'appname in special help can be only alphanum characters';
    /**
     * @errorCode  input help can use only user function
     */
    const ATTR1209 = 'function "%s" is an internal php function';
    /**
     * @errorCode  input help must be defined in declared file
     */
    const ATTR1210 = 'function "%s" is not defined in "%s" file';
    /**
     * @errorCode  the called function need more arguments
     */
    const ATTR1211 = 'not enough argument call to use function "%s" (need %d arguments)';
    /**
     * @errorCode  the method use for computed must declarer an existed Smart Field
     * @note       triggered in \Anakeen\Core\Internal\SmartElement::specRefreshGen()
     */
    const ATTR1212 = 'unknow output attribut for method "%s" in family "%s"';
    /**
     * @errorCode  declaration of call method is not correct
     * @note       example : ::test()  or myClass::test()
     */
    const ATTR1250 = 'syntax error in method call (phpfunc) for Smart Field "%s" : %s';
    /**
     * @errorCode  call of a method mudt contains '::' characters
     * @note       example : ::test()  or myClass::test()
     */
    const ATTR1251 = 'no "::" delimiter in method call "%s"';
    /**
     * @errorCode  method name must be a valid PHP name
     */
    const ATTR1252 = 'syntax error in method name : "%s"';
    /**
     * @errorCode  method name must be a valid PHP class name
     */
    const ATTR1253 = 'syntax error in class name in method call: "%s"';
    /**
     * @errorCode  call method can be return only one value
     * @note       example : ::test():MY_RET
     */
    const ATTR1254 = 'only one output is possible in method "%s"';
    /**
     * @errorCode  generally when it is in constraint Smart Field
     */
    const ATTR1255 = 'no output is possible in method "%s"';
    /**
     * @errorCode  call of a class mudt must have __invoke
     */
    const ATTR1256 = 'no "__invoke" in class call "%s"';
    /**
     * @errorCode  call method in phpfunc property must be defined in file method or in another class if precised
     */
    const ATTR1260 = 'method  "%s" (context : "%s") is not found for "%s" Smart Field';
    /**
     * @errorCode  call method require more arguments
     */
    const ATTR1261 = 'not enough argument call to use method "%s" (context "%s", need %d arguments) for "%s" Smart Field';
    /**
     * @errorCode  the phpfunc is not correct generally detected on inherited Smart Fields
     */
    const ATTR1262 = 'syntax error in method "%s" phpfunc for "%s" Smart Field : %s';
    /**
     * @errorCode  the phpfunc method must be static if a classname is set
     * @note       example : myClass::myStaticMethod()
     */
    const ATTR1263 = 'method "%s" (context : "%s") is not static phpfunc for "%s" Smart Field';
    /**
     * @errorCode  call method in phpfunc property must be defined in file method or in another class if precised
     * @see        ATTR1260
     * @see        ATTR1261
     * @see        ATTR1262
     * @see        ATTR1263
     */
    const ATTR1265 = 'phpfunc method error in "%s" family : %s';
    /**
     * @errorCode  call method in phpfunc property must be defined in file method or in another class if precised
     */
    const ATTR1266 = 'method  "%s" (context "%s" defined in parent family "%s") is not found for "%s" Smart Field';
    /**
     * @errorCode  enum declaration must be a set of key value
     * @note       example : yellow|Yellow color,red|Red color
     */
    const ATTR1270 = 'syntax error in enum declaration near "%s"  for "%s" Smart Field';
    /**
     * @errorCode  the enum key must be a simple word without accent
     * @note       example : yellow|Yellow color,red|Red color
     */
    const ATTR1271 = 'key must not have accent characters in enum declaration "%s"  for "%s" Smart Field';
    /**
     * @errorCode  the enum key is required
     * @note       example : yellow|Yellow color,red|Red color
     */
    const ATTR1272 = 'key must not be empty in enum declaration "%s"  for "%s" Smart Field';
    /**
     * @errorCode  the enum callable is not found
     * @note       example : Test\One::myItems
     */
    const ATTR1273 = 'Enum callable "%s" not found';
    /**
     * @errorCode  declaration of call constraint is not correct
     * @note       example : ::isInteger(MY_ATTR)  or myClass::isSpecial(MY_ATTR)
     */
    const ATTR1400 = 'syntax error in constraint call for Smart Field "%s" : %s';
    /**
     * @errorCode  call method in constraint require more arguments
     */
    const ATTR1401 = 'not enough argument call to use constraint "%s" (need %d arguments, given %d) for "%s" Smart Field';
    /**
     * @errorCode  call constraint method must be defined in file method or in another class if precised
     */
    const ATTR1402 = 'constraint method "%s" is not found for "%s" Smart Field';
    /**
     * @errorCode  the phpfunc method must be static if a classname is set
     * @note       example : myClass::myStaticMethod()
     */
    const ATTR1403 = 'method "%s" is not static phpfunc for "%s" Smart Field';
    /**
     * @errorCode  the constraint is not correct generally detected on inherited Smart Fields
     */
    const ATTR1404 = 'syntax error in constraint "%s" for "%s" Smart Field : %s';
    /**
     * @errorCode  database type are incompatible with attribute type declaration
     */
    const ATTR1700 = 'database document column are erronous : %s';
    /**
     * @errorCode  due to postgresql limit, sql column number is limited
     * @note       declaration for an Smart Field can create more than one sql column
     */
    const ATTR1701 = 'too many Smart Fields : %d (maximum sql column is %d)';
    /**
     * @errorCode The value of the Smart Field will not be computed because the visibility is 'I'
     */
    const ATTR1800 = "value of Smart Field \"%s\" with phpfunc \"%s\" will not be computed because visibility is \"I\".";
}
