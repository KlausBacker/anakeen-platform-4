<?php

namespace Anakeen\Core\Internal;

class SmartCollectionOperators
{
    /**
     * get operators definition for DSEARCH structure
     *
     */
    public static function getOperators()
    {
        return array(
            "~*" => array(
                "label" => ___("include", "smartOperator"),
                "operand" => array(
                    "left",
                    "right"
                ),
                "dynlabel" => "{left} include {right}", # _("{left} include {right}")
                "slabel" => array(
                    "file" => ___("filename or type include", "smartOperator"),
                    "image" => ___("filename or type include", "smartOperator"),
                    "array" => ___("one value include", "smartOperator"),

                ),
                "sdynlabel" => array(
                    "file" => ___("{left} filename or type include {right}", "smartOperator"),
                    "image" => ___("{left} filename or type include {right}", "smartOperator"),
                    "array" => ___("one value of {left} include {right}", "smartOperator"),

                ),
                "type" => array(
                    "text",
                    "longtext",
                    "htmltext",
                    "ifile",
                    "array",
                    "file",
                    "image"
                )
            ),
            "=~*" => array(
                "label" => ___("title include", "smartOperator"),
                "operand" => array(
                    "left",
                    "right"
                ),
                "dynlabel" => ___("{left} title include {right}", "smartOperator"),
                "slabel" => array(
                    "uid" => ___("last or first name include", "smartOperator"),
                    "docidtitle[]" => ___("one of the titles include", "smartOperator"),
                ), #_("title include") _("last or first name include") _("one of the titles include")
                "sdynlabel" => array(
                    "uid" => ___("{left} last or first name include {right}", "smartOperator"),
                    "docidtitle[]" => ___("one of the titles {left} include {right}", "smartOperator"),
                ), #_("{left} title include {right}") _("one of the titles {left} include {right}")
                "type" => array(
                    "uid",
                    "docid",
                    "account",
                    "docidtitle[]"
                )
            ),


            "=" => array(
                "label" => ___("equal", "smartOperator"),
                "operand" => array(
                    "left",
                    "right"
                ),
                "dynlabel" => ___("{left} equal {right}", "smartOperator"),
                "slabel" => array(
                    "docid" => ___("identificator equal", "smartOperator"),
                    "account" => ___("identificator equal", "smartOperator"),
                    "uid" => ___("system identifiant equal", "smartOperator"),
                ),
                "sdynlabel" => array(
                    "docid" => ___("{left} identifier equal {right}", "smartOperator"),
                    "account" => ___("{left} identifier equal {right}", "smartOperator"),
                    "uid" => ___("{left} system identifier equal {right}", "smartOperator"),
                ),
                "type" => array(
                    "text",
                    "integer",
                    "int",
                    "double",
                    "enum",
                    "date",
                    "time",
                    "timestamp",
                    "money",
                    "color",
                    "docid",
                    "account",
                    "uid"
                )
            ),
            "~^" => array(
                "label" => ___("begin by", "smartOperator"),
                "operand" => array(
                    "left",
                    "right"
                ),
                "dynlabel" => ___("{left} begin by {right}", "smartOperator"),
                "type" => array(
                    "text",
                    "longtext"
                )
            ),
            "!=" => array(
                "label" => ___("not equal", "smartOperator"),
                "operand" => array(
                    "left",
                    "right"
                ),
                "dynlabel" => ___("{left} is not equal {right}", "smartOperator"),
                "sdynlabel" => array(
                    "docid" => ___("{left} identifier not equal {right}", "smartOperator"),
                    "account" => ___("{left} identifier not equal {right}", "smartOperator"),
                    "uid" => ___("{left} system identifier not equal {right}", "smartOperator"),
                ), #_("{left} identifier not equal {right}") _("{left} system identifier not equal {right}")
                "slabel" => array(
                    "docid" => ___("identificator not equal", "smartOperator"),
                    "account" => ___("identificator not equal", "smartOperator"),
                    "uid" => ___("system identifier not equal", "smartOperator"),
                ), #_("identificator not equal") _("system identifier not equal")
                "type" => array(
                    "text",
                    "integer",
                    "int",
                    "double",
                    "enum",
                    "date",
                    "time",
                    "timestamp",
                    "money",
                    "color",
                    "docid",
                    "account",
                    "uid"
                )
            ),
            "!~*" => array(
                "label" => ___("not include", "smartOperator"),
                "operand" => array(
                    "left",
                    "right"
                ),
                "dynlabel" => ___("{left} not include {right}", "smartOperator"),
                "slabel" => array(
                    "file" => ___("filename or type not include", "smartOperator"),
                    "image" => ___("filename or type not include", "smartOperator"),
                    "array" => ___("no value include", "smartOperator"),

                ),
                "sdynlabel" => array(
                    "file" => ___("{left} filename or type not include {right}", "smartOperator"),
                    "image" => ___("{left} filename or type not include {right}", "smartOperator"),
                    "array" => ___("{left} include no value of {right}", "smartOperator"),
                ), #_("{left} include no value of {right}")
                "type" => array(
                    "text",
                    "longtext",
                    "htmltext",
                    "ifile",
                    "array",
                    "file",
                    "image"
                )
            ),
            ">" => array(
                "label" => "&gt;",
                "operand" => array(
                    "left",
                    "right"
                ),
                "dynlabel" => ___("{left} greater than {right}", "smartOperator"),
                "type" => array(
                    "int",
                    "integer",
                    "double",
                    "date",
                    "time",
                    "timestamp",
                    "money"
                )
            ),
            "<" => array(
                "label" => "&lt;",
                "operand" => array(
                    "left",
                    "right"
                ),
                "dynlabel" => ___("{left} lesser than {right}", "smartOperator"),
                "type" => array(
                    "int",
                    "integer",
                    "double",
                    "date",
                    "time",
                    "timestamp",
                    "money"
                )
            ),
            ">=" => array(
                "label" => "⩾",
                "operand" => array(
                    "left",
                    "right"
                ),
                "dynlabel" => ___("{left} greater or equal to {right}", "smartOperator"),
                "type" => array(
                    "int",
                    "integer",
                    "double",
                    "date",
                    "time",
                    "timestamp",
                    "money"
                )
            ),
            "<=" => array(
                "label" => "⩽",
                "operand" => array(
                    "left",
                    "right"
                ),
                "dynlabel" => ___("{left} lesser or equal to {right}", "smartOperator"),
                "type" => array(
                    "int",
                    "integer",
                    "double",
                    "date",
                    "time",
                    "timestamp",
                    "money"
                )
            ),
            "is null" => array(
                "label" => ___("is empty", "smartOperator"),
                "operand" => array(
                    "left"
                ),
                "dynlabel" => ___("{left} is null", "smartOperator"),
            ), # _("{left} is null"),
            "is not null" => array(
                "label" => ___("is not empty", "smartOperator"),
                "operand" => array(
                    "left"
                ),
                "dynlabel" => ___("{left} is not empty", "smartOperator"),
            ), # _("{left} is not empty"),
            "><" => array(
                "label" => ___("between", "smartOperator"),
                "operand" => array(
                    "left",
                    "min",
                    "max"
                ),
                "dynlabel" => ___("{left} is between {min} and {max}", "smartOperator"),
                "type" => array(
                    "int",
                    "integer",
                    "double",
                    "date",
                    "time",
                    "timestamp",
                    "money"
                )
            ),
            "~y" => array(
                "label" => ___("one value equal", "smartOperator"),
                "operand" => array(
                    "left",
                    "right"
                ),
                "dynlabel" => "{left} one value equal {right}", # _("{left} one value equal {right}")
                "slabel" => array(
                    "docid[]" => ___("one id equal", "smartOperator"),
                    "account[]" => ___("one id equal", "smartOperator"),
                ),
                "sdynlabel" => array(
                    "docid[]" => ___("{left} one id equal {right}", "smartOperator"),
                    "account[]" => ___("{left} one id equal {right}", "smartOperator"),
                ),
                "type" => array(
                    "array",
                    "docid[]",
                    "account[]"
                )
            )
        );
    }
}
