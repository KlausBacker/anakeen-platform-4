/**
 * Created by Alex on 29/04/15.
 */
/**
 * Document's constraints file
 */


/*global define, require, console*/
require([
    'jquery',
    'dcpContextRoot/uiAssets/Families/dsearch/searchCatalog'
], function ($,i18n) {

    var inhibAttr = [true];
    var inhibFunc = [true];
    var inhibCond = true;
    var inhibKeys = [true];

    window.dcp.document.documentController("addEventListener",
        "ready",
        {
            "name": "initConstraints",
            "documentCheck": function isDsearch(document) {
                return ((document.type === "search") && document.renderMode === "edit");
            }
        },
        function initConstraint() {
            if (itemEmpty($(this).documentController("getValues").se_ol)) {
                inhibCond = false;
            }

            $(this).documentController("addEventListener", "attributeArrayChange",
                {
                    "name": "addArray.cons",
                    "documentCheck": function isDSearch(document) {
                        return ((document.type === "search") && document.renderMode === "edit");
                    },
                    "attributeCheck": function isArray(attribute) {
                        if (attribute.id === "se_t_detail") {
                            return true;
                        }
                    }
                },
                function addArrayConstraint(event, document, attribut, type, options) {
                    if (type === "addLine") {
                        inhibKeys[options] = false;
                    }
                    inhibMenu();
                });


            $(this).documentController("addConstraint", {
                    "name": "checkNotEmptyFunc.cons",
                    "documentCheck": function (document) {
                        return ((document.type === "search") && document.renderMode === "edit");
                    },
                    "attributeCheck": function (attribute) {
                        return attribute.id === "se_funcs";
                    }
                },
                /**
                 *  check if functions' input is still empty if you want to save the document
                 *  @response object binded with functions' input that show error messages
                 */
                function checkNotEmptyFunc(response) {
                    var $documentController = $(this), result = [];
                    $(".dcpAttribute__value[name=se_funcs]").each(function eachFuncsCheckNotEmptyFunc(index, myOperator) {
                        var myAttribute = ($documentController.documentController("getValues").se_attrids)[index];
                        if (!itemEmpty(myAttribute)) {
                            if (itemEmpty(myOperator)) {
                                inhibFunc[index] = false;
                                result.push({"message" : i18n.___("Select operator", "dsearch"), "index": index});
                            }
                            else {
                                inhibFunc[index] = true;
                            }
                        }
                    });
                    inhibMenu();
                    return result;
                }
            );

            $(this).documentController("addConstraint", {
                    "name": "checkNotEmptyAttr.cons",
                    "documentCheck": function (document) {
                        return ((document.type === "search") && document.renderMode === "edit");
                    },
                    "attributeCheck": function (attribute) {
                        return attribute.id === "se_attrids";
                    }
                },
                function checkNotEmptyAttributes(response) {
                    var $documentController = $(this), result = [];
                    $(".dcpAttribute__value[name=se_attrids]").each(function eachKeysCheckNotEmptyAttributes(index) {
                        var myAttribute = ($documentController.documentController("getValues").se_attrids)[index];
                        if (itemEmpty(myAttribute)) {
                            inhibAttr[index] = false;
                            result.push({"message" : i18n.___("Empty attribute", "dsearch"), "index": index});
                        }
                        else {
                            inhibAttr[index] = true;
                        }
                    });
                    inhibMenu();
                    return result;
                });


            $(this).documentController("addConstraint", {
                    "name": "checkNotEmptyKeys.cons",
                    "documentCheck": function (document) {
                        return ((document.type === "search") && document.renderMode === "edit");
                    },
                    "attributeCheck": function (attribute) {
                        return attribute.id === "se_keys";
                    }
                },
                /**
                 *  check if keys' input is still empty if you want to save the document
                 *  @response object binded with keys' input that show error messages
                 */
                function checkNotEmptyKeys(response) {
                    var $documentController = $(this), result = [];
                    $(".dcpAttribute__value[name=se_keys]").each(function eachKeysCheckNotEmptyKeys(index) {
                        var myAttribute = ($documentController.documentController("getValues").se_attrids)[index];
                        var myOperator = ($documentController.documentController("getValues").se_funcs)[index];
                        var myKeyword = ($documentController.documentController("getValues").se_keys)[index];
                        if (!itemEmpty(myAttribute)) {
                            if (!itemEmpty(myOperator)) {
                                if (myOperator.value !== "is null" && myOperator.value !== "is not null" && myOperator.value !== "><") {
                                    if (itemEmpty(myKeyword)) {
                                        inhibKeys[index] = false;
                                        result.push({"message" :i18n.___("Select keys", "dsearch"), "index": index});
                                    }
                                    else {
                                        inhibKeys[index] = true;
                                    }
                                }
                                else {
                                    inhibKeys[index] = true;
                                }
                            }
                        }
                    });
                    inhibMenu();
                    return result;
                });


            $(this).documentController("addConstraint", {
                    "name": "checkNotEmptyOl.cons",
                    "documentCheck": function (document) {
                        return ((document.type === "search") && document.renderMode === "edit");
                    },
                    "attributeCheck": function (attribute) {
                        return attribute.id === "se_ol";
                    }
                },
                /**
                 *  check if condition radio list is still empty if you want to save the document
                 *  @response object binded with condition radio list that show error messages
                 */
                function checkNotEmptyFunc(response) {
                    var condition = ($(this).documentController("getValues").se_ol), result = [];

                    if (itemEmpty(condition)) {
                        inhibCond = false;
                        result.push(i18n.___("Select condition", "dsearch"));
                    }
                    else {
                        inhibCond = true;
                    }
                    inhibMenu();
                    return result;
                });
        });

    window.dcp.document.documentController("addEventListener",
        "close",
        {
            "name": "removeConstraintsEditEvent",
            "documentCheck": function(document) {
                return (document.type === "search");
            }
        },
        function() {
            var $this = $(this);
            $this.documentController("removeEventListener", ".cons");
        }
    );


    /**
     * check if an item is empty/undefined
     * @param myItem, the item to test
     * @returns {boolean}
     */
    function itemEmpty(myItem){
        return (myItem === undefined || myItem === null || myItem.value === null || myItem.value ==="");
    }

    function inhibMenu(){
        var boolInhib = true;

        $(".dcpAttribute__value[name=se_attrids]").each(function eachKeysCheckNotEmptyAttributes(index) {
            if (inhibAttr[index]===false || inhibFunc[index]===false || inhibKeys[index]===false){
                boolInhib = false;
            }
        });

        if (!itemEmpty($("ul.menu__content").data("kendoMenu"))) {
            if (inhibCond === true && boolInhib === true) {
                $("ul.menu__content").data("kendoMenu").enable("[data-menu-id=view]", true);
            }
            else {
                $("ul.menu__content").data("kendoMenu").enable("[data-menu-id=view]", false);
            }
        }
        //console.log(inhibAttr, inhibFunc, inhibKeys);

    }

});