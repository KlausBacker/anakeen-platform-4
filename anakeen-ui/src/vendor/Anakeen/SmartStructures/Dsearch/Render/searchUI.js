/**
 * Created by Alex on 27/05/15.
 */
/*global define, require, console*/

const _ = require("underscore");
import i18n from "./searchCatalog";
import searchAttributes from "./searchAttributes";

{
  var myOperators = [];
  var thisOperators = [];
  var myAttributes = [];
  var myWorkflows = [];
  var $attrid;
  var $wf = null;
  var $beforeMeth = [];
  var $checkMeth = false;

  window.dcp.document.documentController(
    "addEventListener",
    "ready",
    {
      name: "addDsearchEvents",
      documentCheck: function(document) {
        return document.renderMode === "edit" && document.type === "search";
      }
    },
    function prepareEvents() {
      $(this).documentController(
        "addEventListener",
        "ready",
        {
          name: "initDivResult.dsearch",
          documentCheck: function isDSearch(document) {
            return document.type === "search";
          }
        },
        function initDivResultEdit(event, document) {
          if (
            document.renderMode === "edit" ||
            document.renderMode === "create"
          ) {
            var $result = $(".result--content");
            if ($result.length === 0) {
              var $div = $('<div class="result--content"/>');
              $div.insertAfter($(".document"));
            }
          } else {
            $(".result--content").remove();
          }
        }
      );

      $(this).documentController(
        "addEventListener",
        "ready",
        {
          name: "searchReady.dsearch",
          documentCheck: function isDSearch(document) {
            return document.type === "search" && document.renderMode === "edit";
          }
        },
        /**
         * Initialize attributes, operators, and workflow states lists
         * Initialize kendo widgets at keys and operators fields
         * Set visibilities according to initial values
         */
        function prepareSearchDocUI(event, document) {
          var $documentController = $(this);
          var famid = $(this).documentController("getValues").se_famid.value;
          var testWorkflow = false;

          if ($(".dcpArray__content[data-attrid=se_t_detail]").length === 0) {
            return;
          }

          /*
                 Hide title field if in creation mode
                 */
          if (document.viewId === "!coreCreation") {
            $(".dcpCustomTemplate--content[data-attrid='ba_title']").hide();
          } else {
            $(".dcpCustomTemplate--content[data-attrid='ba_title']").show();
          }

          $documentController.documentController(
            "addEventListener",
            "beforeClose",
            {
              name: "preventClose.dsearch",
              documentCheck: function isDsearch(document) {
                return document.type === "search";
              }
            },
            function preventCloseDsearch() {
              $("#grid").remove();
            }
          );

          if (famid === null) {
            famid = "";
          }

          searchAttributes(famid)
            .then(function requestAttributesSReady(data) {
              myAttributes = [];
              $.each(data.data, function eachDataAttributesSReady(key, value) {
                myAttributes.push(value);
              });
              /**
               * update the workflow attribute generic value
               */
              if (myAttributes[myAttributes.length - 1].type === "wid") {
                $wf = myAttributes[myAttributes.length - 1];
              } else {
                $wf = null;
              }
              testWorkflow = findIfWorkflow(myAttributes, $documentController);
            })
            .then(function doneFirstSReady() {
              $.getJSON(
                "api/v2/smartstructures/dsearch/operators/",
                function requestOperatorsSReady(data) {
                  myOperators = [];
                  $.each(data.data, function eachDataOperatorsSReady(
                    key,
                    value
                  ) {
                    myOperators.push(value);
                  });
                }
              ).done(function doneSecondSReady() {
                var $r = $.Deferred();
                if (testWorkflow) {
                  $.getJSON(
                    "api/v2/documents/" +
                      famid +
                      "/workflows/states/?allStates=1",
                    function requestWorkflows(data) {
                      myWorkflows = [];
                      $.each(data.data.states, function eachStatesSReady(
                        key,
                        value
                      ) {
                        myWorkflows.push(value);
                      });
                      $r.resolve();
                    }
                  );
                } else {
                  $r.resolve();
                }

                $r.done(function() {
                  $.each(
                    $documentController.documentController("getValues")
                      .se_attrids,
                    function eachDocAttridsSReady($index, myAttribute) {
                      var myChangedAttribute;
                      if (myAttribute !== undefined) {
                        if (!itemEmpty(myAttribute)) {
                          if (
                            myAttribute.value === "activity" ||
                            myAttribute.value === "fixstate"
                          ) {
                            myAttribute.value = "state";
                          }
                          $.each(
                            myAttributes,
                            function eachPersoAttributesSReady(key, value) {
                              if (myAttribute.value === value.id) {
                                myChangedAttribute = {
                                  id: value.id,
                                  label: value.label,
                                  type: value.type
                                };
                                if (myAttribute.value === "state") {
                                  myAttribute.displayValue = value.label;
                                }
                              }
                            }
                          );
                          if (
                            myAttribute.value === "state" &&
                            myChangedAttribute
                          ) {
                            myChangedAttribute.type = "wid";
                          }
                        } else {
                          myChangedAttribute = null;
                        }
                        thisOperators = [];
                        initOperators(
                          myOperators,
                          myChangedAttribute,
                          thisOperators
                        );
                        defineDropDown($index, thisOperators);
                      }
                    }
                  );
                  $(".dcpAttribute__value[name=se_keys]").each(
                    function eachKeysSReady(index) {
                      var $environment = $(this);
                      var $methods = [];
                      var myAttribute = $documentController.documentController(
                        "getValues"
                      ).se_attrids[index];
                      var myOperator = $documentController.documentController(
                        "getValues"
                      ).se_funcs[index];
                      var $type = defineTypeIdAttribute(
                        myAttribute,
                        myAttributes
                      );

                      deleteButton($environment);

                      if (
                        myAttribute !== undefined &&
                        myOperator !== undefined
                      ) {
                        /*
                                     if the widget is a comboBox, the current input is saved in the place where it should be
                                     */
                        if (
                          !itemEmpty(myOperator) &&
                          (myOperator.value === "=" ||
                            myOperator.value === "!=" ||
                            myOperator.value === "~y")
                        ) {
                          if ($type === "enum[]" || $type === "enum") {
                            if ($(this).data("kendoComboBox") === undefined) {
                              $environment[0].aNode = $environment.parent()[0].firstElementChild;
                              initKendoComboBox(famid, $environment, $attrid);
                            }
                          } else if ($type === "docid" || $type === "docid[]") {
                            if ($(this).data("kendoComboBox") === undefined) {
                              $environment[0].aNode = $environment.parent()[0].firstElementChild;
                              initKendoComboBoxRelation(
                                famid,
                                $environment,
                                $attrid
                              );
                            }
                          } else if (
                            !itemEmpty(myOperator) &&
                            $type === "wid"
                          ) {
                            if ($(this).data("kendoComboBox") === undefined) {
                              $environment[0].aNode = $environment.parent()[0].firstElementChild;
                              initKendoComboBoxWorkflow(
                                $environment,
                                $documentController
                              );
                            }
                          }
                        }
                        /**
                         * If the widget is a datePicker, an another input is created to link the datePicker with
                         */
                        if (
                          !itemEmpty(myOperator) &&
                          ($type === "date" ||
                            $type === "timestamp" ||
                            $type === "time")
                        ) {
                          if ($environment.parent()[0].children.length === 1) {
                            var $input = $("<input />").attr({ type: "text" });
                            $input.insertBefore($environment[0]);
                            $environment.hide();
                            var date = $($environment).val();
                            var elem;

                            if (date && date.indexOf("(") === -1) {
                              $($input).val(date);
                            }

                            if ($type === "date") {
                              initDatePicker($input, index);
                            } else if ($type === "timestamp") {
                              initDateTimePicker($input, index);
                            } else if ($type === "time") {
                              initTimePicker($input, index);
                            }

                            if (date.indexOf("(") !== -1) {
                              $($input).val(date);
                            }
                            // $input[0].disabled = "true";
                          }
                        }

                        /**
                         * If attribute has a method, define associated dropdown and button
                         */
                        $.each(
                          myAttributes,
                          function eachAttributesSReadyMethods(key, value) {
                            if (myAttribute.value === value.id) {
                              $methods = value.methods;
                            }
                          }
                        );

                        if ($methods && $methods.length !== 0) {
                          $(
                            $(".dcpAttribute__content[data-attrid=se_keys]")[
                              index
                            ]
                          )
                            .find("span:first")
                            .addClass("button--on")
                            .removeClass("button--off");
                          $(
                            $(".dcpAttribute__content[data-attrid=se_keys]")[
                              index
                            ]
                          )
                            .find("input:first")
                            .addClass("button--on")
                            .removeClass("button--off");
                          createButtonMethods($environment, $methods);
                        } else {
                          $(
                            $(".dcpAttribute__content[data-attrid=se_keys]")[
                              index
                            ]
                          )
                            .find("span:first")
                            .removeClass("button--on")
                            .addClass("button--off");
                          $(
                            $(".dcpAttribute__content[data-attrid=se_keys]")[
                              index
                            ]
                          )
                            .find("input:first")
                            .removeClass("button--on")
                            .addClass("button--off");
                          deleteButtonMethods($environment);
                        }
                      }
                    }
                  );
                  $.each(
                    $documentController.documentController("getValues")
                      .se_funcs,
                    function eachDocFuncsSReady($index, myOperator) {
                      setVisibility(myOperator, $index, $documentController);
                    }
                  );

                  $(".dcpTab__content").removeClass("dcpTab--loading");
                });
              });
              conditionVisibility($documentController);
            });
        }
      );

      $(this).documentController(
        "addEventListener",
        "change",
        {
          name: "searchVisibilityRevChanged.dsearch",
          documentCheck: function isDSearch(document) {
            return document.type === "search" && document.renderMode === "edit";
          },
          attributeCheck: function isFamily(attribute) {
            if (attribute.id === "se_latest") {
              return true;
            }
          }
        },
        /**
         * update the displayValue of workflows when revision value changed
         */
        function displayVisRevisionChange() {
          var $documentController = $(this);
          findIfWorkflow(myAttributes, $documentController);

          var typeRevision = $(this).documentController("getValues").se_latest
            .value;
          var myObject;
          var dataWorkflow = [];
          _.each(myWorkflows, function eachPersoWorkflowsLatestChanged(item) {
            if (
              typeRevision === "fixed" ||
              typeRevision === "allfixed" ||
              typeRevision === "lastfixed"
            ) {
              myObject = {
                id: item.id,
                label: item.label
              };
            } else if (item.activity !== "") {
              if (typeRevision === "yes") {
                myObject = {
                  id: item.id,
                  label: item.activity
                };
              } else {
                myObject = {
                  id: item.id,
                  label: item.label + "/" + item.activity
                };
              }
            } else {
              if (typeRevision === "yes") {
                myObject = {
                  id: item.id,
                  label: item.label
                };
              } else {
                myObject = {
                  id: item.id,
                  label: item.label
                };
              }
            }
            dataWorkflow.push(myObject);
          });

          $(".dcpAttribute__value[name=se_keys]").each(
            function eachKeysLatestChanged() {
              if ($(this).data("kendoComboBox") !== undefined) {
                var $dataSource = new kendo.data.DataSource({
                  data: dataWorkflow,
                  dataValueField: "id",
                  dataTextField: "label"
                });
                $(this)
                  .data("kendoComboBox")
                  .setDataSource($dataSource);
              }
            }
          );
        }
      );

      $(this).documentController(
        "addEventListener",
        "change",
        {
          name: "searchFuncsAttributeChanged.dsearch",
          documentCheck: function isDSearch(document) {
            return document.type === "search" && document.renderMode === "edit";
          },
          attributeCheck: function isFamily(attribute) {
            if (attribute.id === "se_attrids") {
              return true;
            }
          }
        },
        /**
         * re-initialized or update a raw if an attribute changed
         * @param event unused
         * @param document unused
         * @param attribute unused
         * @param values current, previous and initial attribute value
         */
        function displayAttrChange(event, document, attribute, values) {
          conditionVisibility($(this));
          var $nodeToSave = null;
          var $parent = null;
          var $index = null;
          var $methods = [];
          var famid = $(this).documentController("getValues").se_famid.value;
          var $documentController = $(this);
          var current = values.current;
          var prev = values.previous;

          var $environment = null;
          var $funcEl = null;
          var $displayAttribute;

          if (current.length !== prev.length) {
            return;
          }
          $.each(current, function eachCurrentAttridsChanged(key) {
            if (current[key] !== prev[key]) {
              $index = key;
            }
          });

          $(".dcpAttribute__value[name=se_funcs]").each(
            function eachFuncsAttridsChanged(key, value) {
              if (key === $index) {
                $environment = $(this);
                $funcEl = value;
              }
            }
          );
          $(".dcpAttribute__value[name=se_attrids]").each(
            function eachAttridsAttridsChanged(key) {
              if (key === $index) {
                $displayAttribute = $(this);
              }
            }
          );
          var myAttribute = $documentController.documentController("getValues")
            .se_attrids[$index];
          var myOperator = $documentController.documentController("getValues")
            .se_funcs[$index];
          var myChangedAttribute;
          var attributeExists = false;
          var $type;
          var $seKeys = [];

          // key values reloaded
          if (!itemEmpty(myOperator) && !itemEmpty(myAttribute)) {
            if (
              myOperator.value !== "is null" &&
              myOperator.value !== "is not null" &&
              myOperator.value !== "><"
            ) {
              $documentController.documentController("setValue", "se_keys", {
                value: "",
                index: $index
              });
            } else {
              $documentController.documentController("setValue", "se_keys", {
                value: "foo",
                index: $index
              });
            }

            _.each(myAttributes, function eachAttributesDefineTypeId(data) {
              if (data.id === myAttribute.value) {
                attributeExists = true;
              }
            });
          } else {
            attributeExists = true;
          }

          $seKeys = $(".dcpAttribute__value[name=se_keys]");
          if (!itemEmpty(myAttribute) && attributeExists) {
            $documentController.documentController(
              "cleanAttributeErrorMessage",
              "se_attrids"
            );

            $.each(
              $documentController.documentController("getValues").se_attrids,
              function eachAttridsAttributesFamilychanged(key, val) {
                var attrId = val.value;
                var $controle = 0;

                $.each(
                  myAttributes,
                  function eachNewDataattributesAttributesFamilychanged(
                    mkey,
                    mval
                  ) {
                    if (attrId === mval.id) {
                      $controle = 1;
                    }
                  }
                );
                if ($controle === 0 && !itemEmpty(attrId)) {
                  $documentController.documentController(
                    "setAttributeErrorMessage",
                    "se_attrids",
                    i18n.___("Invalid attribute", "dsearch"),
                    key
                  );
                }
              }
            );

            var $init = false;
            if ($funcEl !== undefined) {
              if ($funcEl.parentElement !== undefined) {
                if ($funcEl.parentElement.style.visibility === "hidden") {
                  $funcEl.parentElement.style.visibility = "visible";
                }
                $funcEl.parentElement.offsetParent.style.visibility = "visible";
              }
            }
            if (myAttribute.value !== null) {
              $.each(myAttributes, function eachPersoAttributesAttridsChanged(
                key,
                value
              ) {
                if (myAttribute.value === value.id) {
                  myChangedAttribute = {
                    id: value.id,
                    label: value.label,
                    type: value.type
                  };
                  if (myChangedAttribute.type === undefined) {
                    myChangedAttribute.type = "wid";
                  }
                }
              });
            } else {
              myChangedAttribute = null;
            }

            thisOperators = [];
            initOperators(myOperators, myChangedAttribute, thisOperators);

            if ($environment.data("kendoDropDownList") !== undefined) {
              $environment.data("kendoDropDownList").destroy();
            }
            defineDropDown($index, thisOperators);
            myOperator.value = thisOperators[0].monId;
            $documentController.documentController("setValue", "se_funcs", {
              value: thisOperators[0].monId,
              index: $index
            });
            $environment.data("kendoDropDownList").select(
              $environment
                .data("kendoDropDownList")
                .ul.children()
                .eq(0)
            );
            $seKeys.each(function eachKeysFirstEnvAttridsChanged(key) {
              if (key === $index) {
                $environment = $(this);
              }
            });
            /**
             * delete everything there was before on the keys field
             */
            if ($environment.closest("div").find("button").length !== 0) {
              if ($checkMeth) {
                $environment
                  .closest("div")
                  .find("button")
                  .trigger("click");
              }
            }
            deleteButtonMethods($environment);
            if (
              $environment.parent()[0].children.length === 2 ||
              $environment.parent()[0].children.length === 3
            ) {
              destroyDatePicker($environment);
            }
            if (
              $environment.data("kendoComboBox") !== undefined &&
              $environment[0].aNode !== undefined
            ) {
              $environment.data("kendoComboBox").destroy();
              $nodeToSave = $environment[0].aNode;
              $parent = $environment.parent()[0].parentElement;
              $parent.firstElementChild.remove();
              $parent.insertBefore($nodeToSave, $parent.lastElementChild);
              $nodeToSave.style.display = "block";
            }
            $type = defineTypeIdAttribute(myAttribute, myAttributes);
            if (myAttribute !== undefined && myOperator !== undefined) {
              /*
                         if the widget is a comboBox, the current input is saved in the place where it should be
                         */
              if (
                !itemEmpty(myOperator) &&
                (myOperator.value === "=" ||
                  myOperator.value === "!=" ||
                  myOperator.value === "~y")
              ) {
                if ($type === "enum[]" || $type === "enum") {
                  if ($environment.data("kendoComboBox") === undefined) {
                    $seKeys.each(function eachKeysIEnumAttridsChanged(key) {
                      if (key === $index) {
                        $init = true;
                        $environment[0].aNode = $environment.parent()[0].firstElementChild;
                        initKendoComboBox(famid, $environment, $attrid);
                      }
                    });
                  }
                } else if ($type === "docid" || $type === "docid[]") {
                  if ($environment.data("kendoComboBox") === undefined) {
                    $seKeys.each(function eachKeysIRelationAttridsChanged(key) {
                      if (key === $index) {
                        $init = true;
                        $environment[0].aNode = $environment.parent()[0].firstElementChild;
                        initKendoComboBoxRelation(famid, $environment, $attrid);
                      }
                    });
                  }
                } else if ($type === "wid") {
                  if ($environment.data("kendoComboBox") === undefined) {
                    $seKeys.each(function eachKeysIWorklowAttridsChanged(key) {
                      if (key === $index) {
                        $init = true;
                        $environment[0].aNode = $environment.parent()[0].firstElementChild;
                        initKendoComboBoxWorkflow(
                          $environment,
                          $documentController
                        );
                      }
                    });
                  }
                }
              }
              /**
               * If the widget is a datePicker, an another input is created to link the datePicker with
               */
              if (
                !itemEmpty(myOperator) &&
                ($type === "date" || $type === "timestamp" || $type === "time")
              ) {
                if ($environment.parent()[0].children.length === 1) {
                  var $input = $("<input />").attr({ type: "text" }); // create a second input to separate value and display value
                  $input.insertBefore($environment[0]);
                  $environment.hide();
                  if ($type === "date") {
                    initDatePicker($input, $index);
                  } else if ($type === "timestamp") {
                    initDateTimePicker($input, $index);
                  } else if ($type === "time") {
                    initTimePicker($input, $index);
                  }
                  //$input[0].disabled = "true";
                }
              }

              /**
               * If attribute has a method, define associated dropdown and button
               */
              $.each(myAttributes, function eachAttributesSReadyMethods(
                key,
                value
              ) {
                if (myAttribute.value === value.id) {
                  $methods = value.methods;
                }
              });

              if ($methods && $methods.length !== 0) {
                $($(".dcpAttribute__content[data-attrid=se_keys]")[$index])
                  .find("span:first")
                  .addClass("button--on")
                  .removeClass("button--off");
                $($(".dcpAttribute__content[data-attrid=se_keys]")[$index])
                  .find("input:first")
                  .addClass("button--on")
                  .removeClass("button--off");
                createButtonMethods($environment, $methods);
              } else {
                $($(".dcpAttribute__content[data-attrid=se_keys]")[$index])
                  .find("span:first")
                  .removeClass("button--on")
                  .addClass("button--off");
                $($(".dcpAttribute__content[data-attrid=se_keys]")[$index])
                  .find("input:first")
                  .removeClass("button--on")
                  .addClass("button--off");
                deleteButtonMethods($environment);
              }
            }
            setVisibility(myOperator, $index, $documentController);
          } else {
            /* reload values */
            if (!attributeExists) {
              $documentController.documentController("setValue", "se_keys", {
                value: "foo",
                index: $index
              });
              $documentController.documentController("setValue", "se_attrids", {
                value: "",
                index: $index
              });
            }
            /*
                     without a selected attribute, widget should be deleted
                     */
            if ($displayAttribute !== undefined) {
              if ($displayAttribute.data("kendoComboBox") !== undefined) {
                $displayAttribute.data("kendoComboBox").value("");
              }
            }
            if ($funcEl !== undefined) {
              if (!itemEmpty($funcEl) && $funcEl.dataset.role !== undefined) {
                // role is undefined if there is no widget
                $funcEl.parentElement.style.visibility = "hidden";
                $funcEl.parentElement.offsetParent.style.visibility = "hidden";
                $funcEl.value = null;
                myOperator.value = null;
              }
              $seKeys.each(function eachKeysSecondEnvAttridsChanged(key) {
                if (key === $index) {
                  $environment = $(this);
                }
              });
              if (
                !itemEmpty($environment) &&
                ($environment.parent()[0].children.length === 3 ||
                  $environment.parent()[0].children.length === 4)
              ) {
                destroyDatePicker($environment);
              }
              if (
                !itemEmpty($environment) &&
                $environment.data("kendoComboBox") !== undefined &&
                $environment[0].aNode !== undefined
              ) {
                $environment.data("kendoComboBox").destroy();
                $nodeToSave = $environment[0].aNode;
                $parent = $environment.parent()[0].parentElement;
                $parent.firstElementChild.remove();
                $parent.insertBefore($nodeToSave, $parent.lastElementChild);
                $nodeToSave.style.display = "block";
              }
              setVisibility(myOperator, $index, $documentController);
            }
          }
        }
      );

      $(this).documentController(
        "addEventListener",
        "change",
        {
          name: "searchFuncsFamidChanged.dsearch",
          documentCheck: function isDSearch(document) {
            return document.type === "search" && document.renderMode === "edit";
          },
          attributeCheck: function isOperator(attribute) {
            if (attribute.id === "se_famid") {
              return true;
            }
          }
        },
        /**
         * update attributes and workflows list if the family change
         */
        function displayFuncsFamidChange() {
          var famid = $(this).documentController("getValues").se_famid.value;
          var $documentController = $(this);
          var testWorkflow = false;

          if (famid === null) {
            famid = "";
          }

          searchAttributes(famid)
            .then(function requestAttributesFamidChanged(data) {
              myAttributes = [];
              $.each(data.data, function eachDataAttributesFamidChanged(
                key,
                value
              ) {
                myAttributes.push(value);
              });
              /**
               * update the workflow attribute generic value
               */
              if (myAttributes[myAttributes.length - 1].type === "wid") {
                $wf = myAttributes[myAttributes.length - 1];
              } else {
                $wf = null;
              }
              testWorkflow = findIfWorkflow(myAttributes, $documentController);
            })
            .then(function doneFamidChanged() {
              if (testWorkflow) {
                $.getJSON(
                  "api/v2/documents/" +
                    famid +
                    "/workflows/states/?allStates=1",
                  function requestWorkflowsFamidChanged(data) {
                    myWorkflows = [];
                    $.each(
                      data.data.states,
                      function eachDataStatesFamidChanged(key, value) {
                        myWorkflows.push(value);
                      }
                    );
                  }
                );
              }
            });
        }
      );

      $(this).documentController(
        "addEventListener",
        "change",
        {
          name: "searchFuncsConditionChanged.dsearch",
          documentCheck: function isDSearch(document) {
            return document.type === "search" && document.renderMode === "edit";
          },
          attributeCheck: function isFamily(attribute) {
            if (attribute.id === "se_ol") {
              return true;
            }
          }
        },
        /**
         * update visibilities when the condition field change
         * initialize operand field if switch to personalization mode
         * @param event unused
         * @param document unused
         * @param attribute unused
         * @param values current, previous and initial
         */
        function displayConditionChange(event, document, attribute, values) {
          var $documentController = $(this);
          var $tabOperands = [];
          conditionVisibility($documentController);
          if (
            (values.current.value === "perso" &&
              values.previous.value === "and") ||
            values.current.value === "and"
          ) {
            $(".dcpAttribute__value[name=se_ols]").each(
              function eachOperandsOlChangedAnd() {
                $tabOperands.push({ value: "and", displayValue: "et" });
              }
            );
            $documentController.documentController(
              "setValue",
              "se_ols",
              $tabOperands
            );
          } else if (
            (values.current.value === "perso" &&
              values.previous.value === "or") ||
            values.current.value === "or"
          ) {
            $(".dcpAttribute__value[name=se_ols]").each(
              function eachOperandOlChangedOr() {
                $tabOperands.push({ value: "or", displayValue: "ou" });
              }
            );
            $documentController.documentController(
              "setValue",
              "se_ols",
              $tabOperands
            );
          }
        }
      );

      $(this).documentController(
        "addEventListener",
        "change",
        {
          name: "searchVisibilityFuncsChanged.dsearch",
          documentCheck: function isDSearch(document) {
            return document.type === "search" && document.renderMode === "edit";
          },
          attributeCheck: function isOperator(attribute) {
            if (attribute.id === "se_funcs") {
              return true;
            }
          }
        },
        /**
         * initialize the keywords field widget according to the attribute's type
         * @param values current, previous and initial of the changed func
         * @param event unused
         * @param document unused
         * @param attribute unused
         *
         */
        function displayFuncChange(event, document, attribute, values) {
          var $parent;
          var $nodeToSave;
          var $index = null;
          var $documentController = $(this);
          var current = values.current;
          var prev = values.previous;
          $.each(current, function eachCurrentFuncsChanged(key) {
            if (current[key] !== prev[key]) {
              $index = key;
            }
          });
          var myOperator = $documentController.documentController("getValues")
            .se_funcs[$index];
          var myAttribute = $documentController.documentController("getValues")
            .se_attrids[$index];
          var famid = $(this).documentController("getValues").se_famid.value;
          var $environment = null;
          var $seKeys = $(".dcpAttribute__value[name=se_keys]");
          var $type;

          $seKeys.each(function eachKeysFuncsChanged(key) {
            if (key === $index) {
              $environment = $(this);
            }
          });
          if (
            !itemEmpty($environment) &&
            $environment.closest("div").find("button").length !== 0
          ) {
            if ($checkMeth) {
              $environment
                .closest("div")
                .find("button")
                .trigger("click");
            }
          }

          $type = defineTypeIdAttribute(myAttribute, myAttributes);
          // key values reloaded
          if (!itemEmpty(myOperator)) {
            if (
              myOperator.value !== "is null" &&
              myOperator.value !== "is not null" &&
              myOperator.value !== "><"
            ) {
              // $documentController.documentController("setValue", "se_keys", {value: "", index: $index});
            } else {
              $documentController.documentController("setValue", "se_keys", {
                value: "",
                index: $index
              });
              if ($type === "date") {
                $environment
                  .parent()
                  .find(".k-picker-wrap input")
                  .val("");
              }
            }
          }
          var $init = false;
          if (myAttribute !== undefined && myOperator !== undefined) {
            /*
                     if the widget is a comboBox, the current input is saved in the place where it should be
                     */
            if (
              !itemEmpty(myOperator) &&
              !$checkMeth &&
              ($type === "enum[]" ||
                $type === "enum" ||
                $type === "docid" ||
                $type === "docid[]" ||
                $type === "wid")
            ) {
              if (
                !itemEmpty($environment) &&
                $environment.data("kendoComboBox") !== undefined &&
                $environment[0].aNode !== undefined
              ) {
                $environment.data("kendoComboBox").destroy();
                $nodeToSave = $environment[0].aNode;
                $parent = $environment.parent()[0].parentElement;
                $parent.firstElementChild.remove();
                $parent.insertBefore($nodeToSave, $parent.lastElementChild);
                $nodeToSave.style.display = "block";
              }
            }
            if (
              !itemEmpty(myOperator) &&
              (myOperator.value === "=" ||
                myOperator.value === "!=" ||
                myOperator.value === "~y") &&
              ($type === "enum[]" || $type === "enum")
            ) {
              if ($environment.data("kendoComboBox") === undefined) {
                $seKeys.each(function eachKeysIEnumFuncsChanged(key) {
                  if (key === $index) {
                    $environment[0].aNode = $environment.parent()[0].firstElementChild;
                    initKendoComboBox(famid, $environment, $attrid);
                  }
                });
              }
            } else if (
              !itemEmpty(myOperator) &&
              ($type === "docid" || $type === "docid[]") &&
              (myOperator.value === "=" ||
                myOperator.value === "!=" ||
                myOperator.value === "~y")
            ) {
              if ($environment.data("kendoComboBox") === undefined) {
                $seKeys.each(function eachKeysIRelationFuncsChanged(key) {
                  if (key === $index) {
                    $environment[0].aNode = $environment.parent()[0].firstElementChild;
                    initKendoComboBoxRelation(famid, $environment, $attrid);
                  }
                });
              }
            } else if (
              !itemEmpty(myOperator) &&
              $type === "wid" &&
              (myOperator.value === "=" || myOperator.value === "!=")
            ) {
              if ($environment.data("kendoComboBox") === undefined) {
                $seKeys.each(function eachKeysIWorkflowsFuncsChanged(key) {
                  if (key === $index) {
                    $environment[0].aNode = $environment.parent()[0].firstElementChild;
                    initKendoComboBoxWorkflow(
                      $environment,
                      $documentController
                    );
                  }
                });
              }
            }
            if (
              !itemEmpty(myOperator) &&
              ($type === "date" || $type === "timestamp" || $type === "time") &&
              (myOperator.value !== "is null" &&
                myOperator.value !== "is not null" &&
                myOperator.value !== "><")
            ) {
              if ($environment.parent()[0].children.length === 1) {
                var $input = $("<input />").attr({ type: "text" }); // create a second input to separate value and display value
                $input.insertBefore($environment[0]);
                $environment.hide();
                if ($type === "date") {
                  initDatePicker($input, $index);
                } else if ($type === "timestamp") {
                  initDateTimePicker($input, $index);
                } else if ($type === "time") {
                  initTimePicker($input, $index);
                }
                //$input[0].disabled = "true";
              }
            }
          } else if ($environment !== undefined) {
            /*
                 without a selected attribute widget should be deleted
                 */
            if (
              !$init &&
              !itemEmpty($environment) &&
              ($environment.parent()[0].children.length === 2 ||
                $environment.parent()[0].children.length === 3)
            ) {
              destroyDatePicker($environment);
            }
            if (
              !$init &&
              !itemEmpty($environment) &&
              $environment.data("kendoComboBox") !== undefined &&
              $environment[0].aNode !== undefined
            ) {
              $environment.data("kendoComboBox").destroy();
              $nodeToSave = $environment[0].aNode;
              $parent = $environment.parent()[0].parentElement;
              $parent.firstElementChild.remove();
              $parent.insertBefore($nodeToSave, $parent.lastElementChild);
              $nodeToSave.style.display = "block";
            }
          }
          if (myOperator !== undefined) {
            setVisibility(myOperator, $index, $documentController);
          }
        }
      );

      $(this).documentController(
        "addEventListener",
        "attributeArrayChange",
        {
          name: "searchFuncsAddArray.dsearch",
          documentCheck: function isDSearch(document) {
            return document.type === "search" && document.renderMode === "edit";
          },
          attributeCheck: function isArray(attribute) {
            if (attribute.id === "se_t_detail") {
              return true;
            }
          }
        },
        /**
         * set all previous parameters to a new line
         * as visibilities, button deletion...
         * @param event unused
         * @param document unused
         * @param attribut unused
         * @param type unused
         * @param options raw of the button to delete
         */
        function displayChange(event, document, attribut, type, options) {
          var $documentController = $(this);
          var $environment = null;
          if (type === "addLine") {
            var $funcEl;
            var $funcInput = $documentController.documentController("getValues")
              .se_funcs[options];
            $(".dcpAttribute__value[name=se_funcs]").each(
              function eachFuncsArrayModified(key, val) {
                if (key === options) {
                  $funcEl = val;
                }
              }
            );
            if ($funcEl !== undefined) {
              $funcEl.offsetParent.style.visibility = "hidden";
              $funcEl.value = null;
              $funcInput.value = null;
            }
            $(".dcpAttribute__value[name=se_keys]").each(
              function eachKeysArrayModified(key) {
                if (key === options) {
                  $environment = $(this);
                }
              }
            );
            var myOperator = $documentController.documentController("getValues")
              .se_funcs[options];
            setVisibility(myOperator, options, $documentController);
            deleteButton($environment);
          }
          conditionVisibility($documentController);
        }
      );
    }
  );

  window.dcp.document.documentController(
    "addEventListener",
    "close",
    {
      name: "removeDsearchEvent",
      documentCheck: function(document) {
        return document.type === "search";
      }
    },
    function() {
      var $this = $(this);
      $this.documentController("removeEventListener", ".dsearch");
    }
  );

  /**
   * set visibilities according to condition field / swap classes to  make css easier
   * set visibilities of parenthesis and operand fields
   * @param $documentController of current document
   */
  function conditionVisibility($documentController) {
    var $condition = $documentController.documentController("getValues").se_ol;
    if ($condition && $condition.value !== "perso") {
      $(".dcpArray__content[data-attrid=se_t_detail] > table")
        .removeClass("dcpArray--custom")
        .addClass("dcpArray--not_custom");
      $documentController.documentController("hideAttribute", "se_ols");
      $('.dcpArray__head__cell[data-attrid="se_ols"]').hide();
      $documentController.documentController("hideAttribute", "se_rightp");
      $('.dcpArray__head__cell[data-attrid="se_rightp"]').hide();
      $documentController.documentController("hideAttribute", "se_leftp");
      $('.dcpArray__head__cell[data-attrid="se_leftp"]').hide();
    } else {
      $(".dcpArray__content[data-attrid=se_t_detail] > table")
        .removeClass("dcpArray--not_custom")
        .addClass("dcpArray--custom");
      $documentController.documentController("showAttribute", "se_ols");
      $('.dcpArray__head__cell[data-attrid="se_ols"]').show();
      $documentController.documentController("showAttribute", "se_rightp");
      $('.dcpArray__head__cell[data-attrid="se_rightp"]').show();
      $documentController.documentController("showAttribute", "se_leftp");
      $('.dcpArray__head__cell[data-attrid="se_leftp"]').show();
      $(".dcpAttribute__value[name=se_ols]").each(function(key, val) {
        if (key === 0) {
          $(val.closest("div")).hide();
        } else {
          $(val.closest("div")).show();
        }
      });
    }
  }

  /**
   * remove a bootstrap button from a specific place
   * @param $environment place to remove the button
   */
  function deleteButton($environment) {
    var $parent = $($environment).closest("div");
    if ($parent[0] !== undefined) {
      var $button = $parent[0].lastElementChild;
      $button.remove();
    }
  }

  /**
   * initialize a datePicker kendo widget
   * @param $environment place to put the widget
   * @param $index index of the raw
   */
  function initDatePicker($environment, $index) {
    $environment.kendoDatePicker({
      parseFormats: ["yyyy-MM-dd"],
      format: null, // standard format depends of the user's langage
      /*
             trigger a fonction that change the value of the date from the displayValue according to ISO 8601
             */
      change: function changeDatePickerValure() {
        var keywordObj = null;
        $(".dcpAttribute__value[name=se_keys]").each(
          function eachKeysChangeDatePicker(key, value) {
            if (key === $index) {
              keywordObj = value;
            }
          }
        );
        var date = this.value();
        if (date) {
          var jour;
          if (date.getDate() / 10 < 1) {
            jour = "0" + date.getDate();
          } else jour = date.getDate();
          var mois;
          if ((date.getMonth() + 1) / 10 < 1) {
            mois = date.getMonth() + 1;
            mois = "0" + mois;
          } else mois = date.getMonth() + 1;
          $(keywordObj).val(date.getFullYear() + "-" + mois + "-" + jour);
        } else {
          $(keywordObj).val("");
        }
        $(keywordObj).trigger("change");
      }
    });
  }

  /**
   * initialize a datePicker kendo widget
   * @param $environment place to put the widget
   * @param $index index of the raw
   */
  function initDateTimePicker($environment, $index) {
    $environment.kendoDateTimePicker({
      parseFormats: [
        "yyyy-MM-dd HH:mm:ss",
        "yyyy-MM-ddTHH:mm:ss",
        "yyyy-MM-ddTHH:mm"
      ],
      timeFormat: "HH:mm",
      format: null, // standard format depends of the user's langage
      /*
             trigger a fonction that change the value of the date from the displayValue according to ISO 8601
             */
      change: function changeDatePickerValure() {
        var keywordObj = null;
        $(".dcpAttribute__value[name=se_keys]").each(
          function eachKeysChangeDatePicker(key, value) {
            if (key === $index) {
              keywordObj = value;
            }
          }
        );

        var timeDate = this.value();
        var sTimeDate = "";
        if (timeDate) {
          sTimeDate =
            timeDate.getFullYear() +
            "-" +
            searchPadNumber(timeDate.getMonth() + 1) +
            "-" +
            searchPadNumber(timeDate.getDate()) +
            " " +
            searchPadNumber(timeDate.getHours()) +
            ":" +
            searchPadNumber(timeDate.getMinutes()) +
            ":" +
            searchPadNumber(timeDate.getSeconds());
        }
        $(keywordObj).val(sTimeDate);
        $(keywordObj).trigger("change");
      }
    });
  }

  /**
   * initialize a datePicker kendo widget
   * @param $environment place to put the widget
   * @param $index index of the raw
   */
  function initTimePicker($environment, $index) {
    $environment.kendoTimePicker({
      timeDataFormat: ["HH:mm", "HH:mm:ss"],
      format: null, // standard format depends of the user's langage
      /*
             trigger a fonction that change the value of the date from the displayValue according to ISO 8601
             */
      change: function changeDatePickerValure() {
        var keywordObj = null;
        $(".dcpAttribute__value[name=se_keys]").each(
          function eachKeysChangeDatePicker(key, value) {
            if (key === $index) {
              keywordObj = value;
            }
          }
        );
        var timeDate = this.value();
        var time = "";
        if (timeDate) {
          time =
            searchPadNumber(timeDate.getHours()) +
            ":" +
            searchPadNumber(timeDate.getMinutes());
        }
        $(keywordObj).val(time);
        $(keywordObj).trigger("change");
      }
    });
  }

  function searchPadNumber(number) {
    if (number < 10) {
      return "0" + number;
    }
    return number;
  }

  /**
   * remove a datePicker kendo widget from the DOM
   * @param $environment place to delete the widget
   */
  function destroyDatePicker($environment) {
    var $parent = $environment.parent()[0];
    $parent.children[0].remove();
    $environment.show();
  }

  /**
   * define attribute type and id
   * @param myAttribute selected attribute
   * @param myAttributes list of attributes
   */
  function defineTypeIdAttribute(myAttribute, myAttributes) {
    var $atype = "";
    if (!itemEmpty(myAttribute)) {
      _.each(myAttributes, function eachAttributesDefineTypeId(data) {
        if (data.id === myAttribute.value) {
          $atype = data.type;
        }
      });
      $attrid = myAttribute.value;
    } else {
      $atype = "";
    }
    if (!$atype && myAttribute.value === "activity") {
      $atype = "wid";
      myAttribute.value = "state";
    }

    return $atype;
  }

  /**
   * Initialize workflow keywords kendoComboBox widget
   * @param $environment place to put the widget
   * @param $documentController of current document
   */
  function initKendoComboBoxWorkflow($environment, $documentController) {
    var typeRevision = $documentController.documentController("getValues")
      .se_latest.value;
    var myObject;
    var dataWorkflow = [];
    /*
         initialize the workflows keys list, depending on the revision value
         */
    _.each(myWorkflows, function eachWorkflowsIWorkflow(item) {
      if (
        typeRevision === "fixed" ||
        typeRevision === "allfixed" ||
        typeRevision === "lastfixed"
      ) {
        myObject = {
          id: item.id,
          label: item.label
        };
      } else if (item.activity !== "") {
        if (typeRevision === "yes") {
          myObject = {
            id: item.id,
            label: item.activity
          };
        } else {
          myObject = {
            id: item.id,
            label: item.label + "/" + item.activity
          };
        }
      } else {
        if (typeRevision === "yes") {
          myObject = {
            id: item.id,
            label: item.label
          };
        } else {
          myObject = {
            id: item.id,
            label: item.label
          };
        }
      }
      dataWorkflow.push(myObject);
    });
    $environment.kendoComboBox({
      width: 200,
      filter: "contains",
      clearButton: false,
      minLength: 0,
      dataValueField: "id",
      dataTextField: "label",
      dataSource: dataWorkflow
    });
    $environment.data("kendoComboBox").list.css("min-width", "300px");
  }

  /**
   * define the dropDownList kendo widget on the operator field @ the specified index
   * @param $index index of the raw
   * @param myOperators list  of operators
   */
  function defineDropDown($index, myOperators) {
    $(".dcpAttribute__value[name=se_funcs]").each(function eachFuncsDefineDD(
      key
    ) {
      if (key === $index) {
        /*
                 update or create the widget
                 */
        if ($(this).data("kendoDropDownList") !== undefined) {
          var $dataSource = new kendo.data.DataSource({
            data: myOperators
          });
          $(this)
            .data("kendoDropDownList")
            .setDataSource($dataSource);
        } else {
          $(this).kendoDropDownList({
            dataSource: myOperators,
            dataTextField: "monTitre",
            dataValueField: "monId",
            template: '<span class="k-state-default">#= monTitre #</span>',
            valueTemplate: "<span> #= monTitre# </span>",
            index: 0
          });
          $(this)
            .data("kendoDropDownList")
            .list.css("min-width", "300px");
        }
        /*
                 remove bootstrap button
                 */
        if ($(this)[0].parentElement.parentElement.children.length === 2) {
          $(this)[0].parentElement.parentElement.lastElementChild.remove();
        }
      }
    });
  }

  /**
   * Initialize relation keywords kendoComboBox widget
   * @param famid id of the current family
   * @param $environment place to put the widget
   * @param attrid id of the current attribute
   */
  function initKendoComboBoxRelation(famid, $environment, attrid) {
    $environment.kendoComboBox({
      width: 200,
      filter: "contains",
      dataValueField: "id",
      dataTextField: "htmlTitle",
      clearButton: false,
      dataSource: {
        type: "json",
        serverFiltering: true,
        transport: {
          /**
           * function to get data
           * @param options param to return success or error data
           */
          read: function readDatasIRelation(options) {
            var filter = "";
            if (options.data.filter !== undefined) {
              if (options.data.filter.filters[0] !== undefined) {
                filter = options.data.filter.filters[0].value;
              }
            }
            $.ajax({
              type: "GET",
              url:
                "api/v2/smartstructures/dsearch/relations/" +
                famid +
                "/" +
                attrid +
                "?slice=25&offset=0&keyword=" +
                filter,
              dataType: "json",
              success: function succesRequestRelationsIRelation(result) {
                var info = [];
                _.each(result.data, function eachResultRelationsIRelation(
                  item
                ) {
                  info.push({
                    id: item.id,
                    htmlTitle: item.htmlTitle
                  });
                });
                options.success(info);
              },
              error: function errorRequestRelationsIRelation(result) {
                options.error(result);
              }
            });
          }
        }
      }
    });
    $environment.data("kendoComboBox").list.css("min-width", "300px");
  }

  /**
   * Initialize enum keywords kendoComboBox widget
   * @param famid id of the current family
   * @param $environment place to put the widget
   * @param attrid id of the current attribute
   */
  function initKendoComboBox(famid, $environment, attrid) {
    $environment.kendoComboBox({
      width: 200,
      filter: "contains",
      clearButton: false,
      dataValueField: "value",
      dataTextField: "displayValue",
      dataSource: {
        type: "json",
        serverFiltering: true,
        transport: {
          /**
           * function to get data
           * @param options param to return success or error data
           */
          read: function readDatasIEnum(options) {
            var filter = "";
            if (
              options.data.filter !== undefined &&
              options.data.filter.filters[0] !== undefined
            ) {
              filter = {
                keyword: options.data.filter.filters[0].value,
                operator: options.data.filter.filters[0].operator
              };
            }
            $.ajax({
              type: "GET",
              url: "api/v2/families/" + famid + "/enumerates/" + attrid,
              data: filter,
              dataType: "json",
              success: function succesRequestEnumsIEnum(result) {
                var info = [];
                _.each(result.data.enumItems, function eachResultEnumsIEnum(
                  enumItem
                ) {
                  info.push({
                    value: enumItem.key,
                    displayValue: enumItem.label
                  });
                });
                options.success(info);
              },
              error: function errorRequestEnumsIEnum(result) {
                options.error(result);
              }
            });
          }
        }
      }
    });
    $environment.data("kendoComboBox").list.css("min-width", "300px");
  }

  /**
   * Initialize list of operator with the type of the selected attribute
   * @param data list of all operators
   * @param myChangedAttribute attribute selected
   * @param operators list to init
   */
  function initOperators(data, myChangedAttribute, operators) {
    if (myChangedAttribute !== null && myChangedAttribute !== undefined) {
      $.each(data, function eachDataInitOperators(key, value) {
        var myObject;
        if (value.compatibleTypes === null) {
          myObject = {
            monId: value.id,
            monTitre: value.title
          };
          if (value.typedTitle[myChangedAttribute.type] !== undefined) {
            myObject.monTitre = value.typedTitle[myChangedAttribute.type];
          }
          operators.push(myObject);
        } else if (
          /*
                 check if the type is compatible with the operator
                 */
          value.compatibleTypes.indexOf(myChangedAttribute.type) !== -1
        ) {
          myObject = {
            monId: value.id,
            monTitre: value.title
          };
          if (value.typedTitle[myChangedAttribute.type] !== undefined) {
            myObject.monTitre = value.typedTitle[myChangedAttribute.type];
          }
          operators.push(myObject);
        }
      });
    }
  }

  /**
   * check if an item is empty/undefined
   * @param myItem, the item to test
   * @returns {boolean}
   */
  function itemEmpty(myItem) {
    return (
      myItem === undefined ||
      myItem === null ||
      myItem.value === null ||
      myItem.value === ""
    );
  }

  /**
   * find if current family is a workflow
   * replace the workflow attribute by a new based on generic workflow attribute
   * @param $data attribute list
   * @param $documentController
   * @returns {boolean}
   */
  function findIfWorkflow($data, $documentController) {
    var $lastAttribute = $data[$data.length - 1];
    var $revAttribute = $documentController.documentController("getValues")
      .se_latest;
    var myObject;
    if ($lastAttribute.type === "wid" && !itemEmpty($revAttribute)) {
      $data.pop();
      if ($revAttribute.value === "yes") {
        myObject = {
          id: $wf.id,
          label: $wf.label[0],
          label_parent: $wf.parent.label,
          type: "wid"
        };
        $data.push(myObject);
      } else if ($revAttribute.value === "no") {
        myObject = {
          id: $wf.id,
          label: $wf.label[1],
          label_parent: $wf.parent.label,
          type: "wid"
        };
        $data.push(myObject);
      } else {
        myObject = {
          id: $wf.id,
          label: $wf.label[2],
          label_parent: $wf.parent.label,
          type: "wid"
        };
        $data.push(myObject);
      }
      return true;
    }
    return false;
  }

  /**
   * Set keys visibilities
   * @param myOperator operator used to know if you show or hide the keys field
   * @param $index index of the field in the table
   * @param $documentController the document controller of the document
   */
  function setVisibility(myOperator, $index, $documentController) {
    var myKeyword;
    var minorKeyword;
    var visible = false;
    var $environment = null;
    $(".dcpAttribute__value[name=se_keys]").each(function eachKeysSetVisibility(
      key,
      value
    ) {
      if (key === $index) {
        $environment = $(this);
        myKeyword = value;
      }
    });
    minorKeyword = $documentController.documentController("getValues").se_keys[
      $index
    ];
    if (myKeyword !== undefined) {
      var $label = null;

      _.each(myOperators, function eachOperatorsSetVisibility(data) {
        if (myOperator !== undefined) {
          if (myOperator !== null) {
            if (myOperator.value === data.id) {
              $label = data.label;
            }
          }
        }
      });

      if ($label === null) {
        visible = false;
      } else {
        /*
             check if the operator has a "right" operand
             */
        visible = $label.indexOf("{right}") !== -1;
      }
      var $parent = $environment.closest("div");
      if (visible) {
        $parent.show();
      } else {
        $parent.hide();
        myKeyword.value = null;
        minorKeyword.value = null;
        minorKeyword.displayValue = null;
      }
    }
  }

  /**
   * create bootstrap button to show methods associated to current attribute
   * @param $environment
   * @param $methods
   */
  function createButtonMethods($environment, $methods) {
    var $myButton = $('<button class="funcBtn">&Sigma;</button>');
    var $inputs = $($environment.closest("div").find("input"));
    var $index = $beforeMeth.length;
    var $methodInput;
    $beforeMeth[$index] = null;

    $myButton.click(function clickOnMethodsButton() {
      if ($beforeMeth[$index] == null) {
        if ($inputs.length === 2) {
          $($($inputs[0]).closest("span")[0].parentElement).hide();
          $methodInput = $($inputs[$inputs.length - 1]);
          $methodInput.show();

          $methodInput.val("");
          $methodInput.trigger("change");
        }
        $beforeMeth[$index] = $inputs[$inputs.length - 1];
        $environment.kendoComboBox({
          width: 200,
          filter: "contains",
          clearButton: false,
          minLength: 0,
          dataValueField: "method",
          dataTextField: "method",
          template: "#: label #",
          dataSource: $methods
        });
        $checkMeth = true;
        $environment.data("kendoComboBox").list.css("min-width", "300px");
        $($environment.closest("div").find("span"))
          .addClass("button--on")
          .removeClass("button--off");
      } else {
        $environment.data("kendoComboBox").destroy();
        var $nodeToSave = $beforeMeth[$index];
        var $parent = $environment.parent()[0].parentElement;
        $parent.children[$inputs.length - 1].remove();
        $parent.insertBefore($nodeToSave, $parent.lastElementChild);
        $nodeToSave.style.display = "block";
        if ($inputs.length === 2) {
          $($inputs[$inputs.length - 1]).hide();
          $($($inputs[0]).closest("span")[0].parentElement).show();
          $($nodeToSave).val("");
          $($nodeToSave).trigger("change");
          $($inputs[0]).val("");
        }
        $checkMeth = false;
        $beforeMeth[$index] = null;
      }
    });
    $myButton.kendoButton();
    $myButton.insertAfter($inputs[$inputs.length - 1]);
  }

  function deleteButtonMethods($environment) {
    $($environment.closest("div").find("button")).remove();
  }
}
