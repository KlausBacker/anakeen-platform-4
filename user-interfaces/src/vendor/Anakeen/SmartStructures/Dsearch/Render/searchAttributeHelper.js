const _ = require("underscore");
import searchAttributes from "./searchAttributes";

export default function searchAttributeHelperProcess(controller) {
  var dataAttributesNew = [];
  var dataAttributes = [];
  var $wf = null;

  controller.addEventListener(
    "ready",
    {
      name: "addDsearchAttributesEvents",
      check: function(document) {
        return document.type === "search";
      }
    },
    function prepareAttributesEvents() {
      controller.addEventListener(
        "ready",
        {
          name: "searchAttributesReady.sAttr",
          check: function isDSearch(document) {
            return document.type === "search";
          }
        },
        /**
         * prepare attributes list and attributes kendo widget
         */
        function prepareAttributesReady() {
          let famid = controller.getValues().se_famid.value;

          if (famid === null) {
            famid = "";
          }
          if ($(".dcpArray__content[data-attrid=se_t_detail]").length === 0) {
            return;
          }

          searchAttributes(famid)
            .then(function requestAttributesAttributesReady(data) {
              dataAttributes = [];
              /**
               * initialize attribute list
               */
              $.each(data.data, function eachDataAttributesReady(key, val) {
                let myObject = {
                  id: val.id,
                  label: val.label,
                  label_parent: val.parent.label,
                  type: val.type
                };
                if (val.type !== "array") {
                  dataAttributes.push(myObject);
                }
              });
              /**
               * initialize the workflow attribute generic value
               */
              if (dataAttributes[dataAttributes.length - 1].type === "wid") {
                $wf = dataAttributes[dataAttributes.length - 1];
              } else {
                $wf = null;
              }
              findIfWorkflow(dataAttributes);
            })
            .then(function doneAttributesReady() {
              $(".dcpAttribute__value[name=se_attrids]").each(function eachAttributesAttributesReady() {
                /**
                 * create/update attributes kendo widget
                 */
                if ($(this).data("kendoComboBox") !== undefined) {
                  const $dataSource = new kendo.data.DataSource({
                    data: dataAttributes,
                    group: { field: "label_parent" }
                  });
                  $(".dcpAttribute__value[name=se_attrids]")
                    .data("kendoComboBox")
                    .setDataSource($dataSource);
                } else {
                  const $environment = $(this);
                  initKendoComboBox(dataAttributes, $environment);
                }
              });
            });
        }
      );

      controller.addEventListener(
        "smartFieldArrayChange",
        {
          name: "searchAttributesAddArray.sAttr",
          check: function isDSearch(document) {
            return document.type === "search";
          },
          smartFieldCheck: function isArray(attribute) {
            if (attribute.id === "se_t_detail") {
              return true;
            }
          }
        },
        /**
         * init attributes kendo widget on a new raw
         */
        function displayChange(event, document, attribut, type, options) {
          if (type === "addLine") {
            $(".dcpAttribute__value[name=se_attrids]").each(function eachAttributesAttributesAddarray(key) {
              if (key === options) {
                var $environment = $(this);
                initKendoComboBox(dataAttributes, $environment);
              }
            });
          }
        }
      );

      controller.addEventListener(
        "smartFieldChange",
        {
          name: "searchAttributesRevChanged.sAttr",
          check: function isDSearch(document) {
            return document.type === "search";
          },
          smartFieldCheck: function isFamily(attribute) {
            if (attribute.id === "se_latest") {
              return true;
            }
          }
        },
        /**
         * Update workflow attribute's value and attributes kendo widget's data
         * triggered if revision value change
         */
        function displayRevisionChange() {
          findIfWorkflow(dataAttributes);
          $(".dcpAttribute__value[name=se_attrids]").each(function eachAttributesAttributesRevchanged() {
            if ($(this).data("kendoComboBox") !== undefined) {
              var $dataSource = new kendo.data.DataSource({
                data: dataAttributes,
                group: { field: "label_parent" }
              });
              $(this)
                .data("kendoComboBox")
                .setDataSource($dataSource);
            }
          });
        }
      );

      controller.addEventListener(
        "smartFieldChange",
        {
          name: "searchAttributesFamilyChanged.sAttr",
          documentCheck: function isDSearch(document) {
            return document.type === "search";
          },
          attributeCheck: function isFamily(attribute) {
            if (attribute.id === "se_famid") {
              return true;
            }
          }
        },
        /**
         * Update attributes list, attributes kendo widget's data and workflow attribute value if necessary,
         * triggered if the family value change
         * @param event standard jQuery event
         * @param document current document object
         * @param attribute current attribute object
         * @param options current, previous, and initial values
         */
        function displayChange(event, document, attribute, options) {
          var famid = controller.getValues().se_famid.value;
          dataAttributesNew = [];
          searchAttributes(famid)
            .then(function requestAttributesAttributesFamilychanged(data) {
              $.each(data.data, function eachDataAttributesFamilychanged(key, val) {
                var myObject = {
                  id: val.id,
                  label: val.label,
                  label_parent: val.parent.label,
                  type: val.type
                };
                if (val.type !== "array") {
                  dataAttributesNew.push(myObject);
                }
              });
              /**
               * update the workflow attribute generic value
               */
              if (dataAttributesNew[dataAttributesNew.length - 1].type === "wid") {
                $wf = dataAttributesNew[dataAttributesNew.length - 1];
              } else {
                $wf = null;
              }
              findIfWorkflow(dataAttributesNew);
            })
            .then(function doneAttributesFamilychanged() {
              if (Array.isArray(controller.getValues().se_attrids)) {
                /**
                 * Test if attributes selected are still in current family
                 * Show an error message if not
                 */
                controller.cleanSmartFieldErrorMessage("se_attrids");
                $.each(controller.getValues().se_attrids, function eachAttridsAttributesFamilychanged(key, val) {
                  var attrId = val.value;
                  var $controle = 0;

                  $.each(dataAttributesNew, function eachNewDataattributesAttributesFamilychanged(mkey, mval) {
                    if (attrId === mval.id) {
                      $controle = 1;
                    }
                  });
                  if ($controle === 0 && !itemEmpty(attrId)) {
                    controller.setSmartFieldErrorMessage("se_attrids", "TODO translate", key);
                    var myOperator = controller.getValues().se_funcs[options];
                    setVisibility(myOperator, options);
                  }
                });
              }
              dataAttributes = dataAttributesNew;
              $(".dcpAttribute__value[name=se_attrids]").each(function eachAttributesAttributesFamilychanged() {
                /**
                 * Update attributes kendo widget's data
                 */
                if ($(this).data("kendoComboBox") !== undefined) {
                  var $dataSource = new kendo.data.DataSource({
                    data: dataAttributes,
                    group: { field: "label_parent" }
                  });
                  $(this)
                    .data("kendoComboBox")
                    .setDataSource($dataSource);
                }
              });
            });
        }
      );
    }
  );

  controller.addEventListener(
    "close",
    {
      name: "removeDsearchAttributesEvent",
      check: function(document) {
        return document.type === "search";
      }
    },
    function() {
      controller.removeEventListener(".sAttr");
    }
  );

  /**
   * Initialize a kendoComboBox widget on a specific object
   * @param dataAttributes list of attributes, used to set the data
   * @param $environment place to put the widget
   */
  function initKendoComboBox(dataAttributes, $environment) {
    $environment.kendoComboBox({
      width: 200,
      placeholder: "Choisir attribut",
      clearButton: false,
      filter: "contains",
      minLength: 0,
      dataValueField: "id",
      dataTextField: "label",
      dataSource: {
        data: dataAttributes,
        group: { field: "label_parent" }
      }
    });
    $environment.data("kendoComboBox").list.css("min-width", "300px");
  }

  /**
   * check if an item is empty/undefined
   * @param myItem, the item to test
   * @returns {boolean}
   */
  function itemEmpty(myItem) {
    return myItem === undefined || myItem === null || myItem.value === null || myItem.value === "";
  }

  /**
   * Set keys visibilities
   * @param myOperator operator used to know if you show or hide the keys field
   * @param $index index of the field in the table
   */
  function setVisibility(myOperator, $index) {
    var myKeyword;
    var minorKeyword;
    var visible = false;
    $(".dcpAttribute__value[name=se_keys]").each(function eachKeysSetVisibility(key, value) {
      if (key === $index) {
        myKeyword = value;
      }
    });
    minorKeyword = controller.getValues().se_keys[$index];
    if (myKeyword !== undefined) {
      var $label = null;

      _.each([], function emptyEachSetVisibility(data) {
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
      var $parent = $(myKeyword).closest("div");
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
   * find if current family is a workflow
   * replace the workflow attribute by a new based on generic workflow attribute
   * @param $data attribute list
   * @param $documentController
   * @returns {boolean}
   */
  function findIfWorkflow($data) {
    var $lastAttribute = $data[$data.length - 1];
    var $revAttribute = controller.getValues().se_latest;
    var myObject;
    if ($lastAttribute.type === "wid") {
      $data.pop();
      if ($revAttribute.value === "yes") {
        myObject = {
          id: $wf.id,
          label: $wf.label[0],
          label_parent: $wf.label_parent,
          type: "wid"
        };
        $data.push(myObject);
      } else if ($revAttribute.value === "no") {
        myObject = {
          id: $wf.id,
          label: $wf.label[1],
          label_parent: $wf.label_parent,
          type: "wid"
        };
        $data.push(myObject);
      } else {
        myObject = {
          id: $wf.id,
          label: $wf.label[2],
          label_parent: $wf.label_parent,
          type: "wid"
        };
        $data.push(myObject);
      }
      return true;
    }
    return false;
  }
}
