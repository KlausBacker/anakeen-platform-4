import "./MaskEdit.css";
import $ from "jquery";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.columnmenu";
import "@progress/kendo-ui/js/kendo.dropdownlist";

window.ank.smartElement.globalController.registerFunction("maskEdit", controller => {
  const fitMaskGridToBottom = $tree => {
    if ($tree.length === 1) {
      let kTree = $tree.data("kendoTreeList");

      if (kTree) {
        $tree.height($(window).height() - $tree.offset().top - 4);
        kTree.resize();
      }
    }
  };

  /**
   * add class by type in tr tag
   * @param grid
   */
  const addRowClassName = grid => {
    let items = grid.items();

    // Need to defer because kendo treelist delete custom class after expand/collapse
    window.setTimeout(() => {
      items.each(function addTypeClass() {
        let dataItem = grid.dataItem(this);
        if (dataItem.type) {
          $(this).addClass(" attr-type--" + dataItem.type);
        }
      });
    }, 1);
  };

  const changeVisibility = (rowData, val, index) => {
    controller.setValue("msk_attrids", { value: rowData.attrid, index: index });
    controller.setValue("msk_visibilities", { value: val, index: index });
  };

  const changeNeeded = (rowData, val, index) => {
    controller.setValue("msk_attrids", { value: rowData.attrid, index: index });
    controller.setValue("msk_needeeds", { value: val, index: index });
  };
  controller.addEventListener("ready", function maskViewReady(event, smartElementObject) {
    const maskHeader = $(".mask-header");
    const neededTab = [
      { value: true, text: "Mandatory" },
      { value: false, text: "Optionnal" }
    ];
    const visibilities = controller.getCustomServerData().VISIBILITIES_LABEL;
    const visibilitiesTab = [];
    Object.keys(visibilities).forEach(i => {
      if (i === "-") {
        visibilitiesTab.push({ value: i, text: "Unset" });
      } else if (i !== "I") {
        visibilitiesTab.push({ value: i, text: visibilities[i] });
      }
    });
    const visibilitiesTabNoArray = visibilitiesTab.filter(item => item.value !== "U");
    let $tree = $("#maskGrid");

    if ($tree.length === 0) {
      return;
    }

    $(window).on("resize.mask", () => {
      let $tree = $("#maskGrid");

      if ($tree.length === 1) {
        fitMaskGridToBottom($tree);
      } else {
        $(window).off("resize.mask");
      }
    });
    $tree.kendoTreeList({
      dataSource: {
        transport: {
          read: {
            url: "/api/v2/admin/mask/" + smartElementObject.id + "/visibilities/"
          }
        },
        schema: {
          model: {
            id: "attrid",
            parentId: "parentId",
            expanded: true
          },
          data: response => {
            response.data.forEach(item => {
              item.mNeededLabel = item.mNeeded ? "Mandatory" : "Optional";
              item.neededLabel = item.needed ? "Mandatory" : "Optional";
            });
            return response.data;
          }
        }
      },
      columnMenu: { columns: true },
      filterable: {
        extra: false,
        operators: {
          string: {
            contains: "Contains...",
            startswith: "Starts with..."
          }
        }
      },
      dataBound: e => {
        let grid = e.sender;
        addRowClassName(grid);
        const mVisibilityRow = grid.table.find("tr");
        mVisibilityRow.each((index, row) => {
          const data = grid.dataItem(row);
          $(row)
            .find("input[class='mask-visibility--dropdown']")
            .addClass("mask-visibility--dropdown-" + index);
          const $maskAlteredVisibilityList = $(".mask-visibility--dropdown-" + index);
          if (data.type !== "array") {
            $maskAlteredVisibilityList.kendoDropDownList({
              dataSource: visibilitiesTabNoArray,
              dataTextField: "text",
              dataValueField: "value",
              value: data.mVisibility,
              change: function() {
                changeVisibility(data, this.value(), index);
              }
            });
          } else {
            $maskAlteredVisibilityList.kendoDropDownList({
              dataSource: visibilitiesTab,
              dataTextField: "text",
              dataValueField: "value",
              value: data.mVisibility,
              change: function() {
                changeVisibility(data, this.value(), index);
              }
            });
          }

          $(row)
            .find("input[class='mask-needed--dropdown']")
            .addClass("mask-needed--dropdown-" + index);
          const $maskNeededVisibilityList = $(".mask-needed--dropdown-" + index);
          $maskNeededVisibilityList.kendoDropDownList({
            dataSource: neededTab,
            dataTextField: "text",
            dataValueField: "value",
            value: data.mNeeded,
            change: function() {
              changeNeeded(data, this.value(), index);
            }
          });
          controller.getValue("msk_attrids").forEach((item, i) => {
            if (data.attrid === item.value) {
              controller.setValue("msk_attrids", { value: item.value, index: index, displayValue: item.displayValue });
              controller.setValue("msk_visibilities", {
                value: controller.getValue("msk_visibilities")[i].value,
                index: index,
                displayValue: controller.getValue("msk_visibilities")[i].displayValue
              });
              controller.setValue("msk_needeeds", {
                value: controller.getValue("msk_needeeds")[i].value,
                index: index,
                displayValue: controller.getValue("msk_needeeds")[i].displayValue
              });
            }
          });
        });
      },

      expand: e => {
        let grid = e.sender;
        addRowClassName(grid);
      },

      collapse: e => {
        let grid = e.sender;
        addRowClassName(grid);
      },

      sortable: false,
      columns: [
        {
          field: "label",
          title: "Label"
        },
        {
          field: "mVisibilityLabel",
          width: "12rem",
          title: "Altered visibility",
          attributes: {
            class: "cell--m-visibility"
          },
          template:
            "#if (data.setVisibility === true) {#" +
            '<div class="mask-visibility mask-visibility-set"><input class="mask-visibility--dropdown"/></div>' +
            "# } else if (data.setVisibility === false && data.visibility !== data.mVisibility)" +
            ' {#<div class="mask-visibility mask-visibility-modified"><input class="mask-visibility--dropdown"/></div>#}' +
            'else if (data.mVisibility) {# <div class="mask-visibility"><input class="mask-visibility--dropdown"/></div>  #}#'
        },
        {
          field: "mNeededLabel",
          title: "Altered need",
          width: "10rem",
          filterable: true,
          attributes: {
            class: "cell--m-needed"
          },
          template:
            "#if (data.needed == undefined) {# #} " +
            "else if (data.needed !== data.mNeeded) {#" +
            '<div class="mask-needed mask-needed-set"><input class="mask-needed--dropdown"/></div>' +
            '# } else if (data.needed !== undefined) {# <div class="mask-needed"><input class="mask-needed--dropdown"/></div>' +
            "#}#"
        },
        {
          field: "visibilityLabel",
          type: "text",
          width: "12rem",
          title: "Original visibility",
          attributes: {
            class: "cell--visibility"
          }
        },
        {
          field: "neededLabel",
          title: "Original need",
          attributes: {
            class: "cell--needed"
          },
          width: "10rem",
          filterable: true,
          template: '<div class="mask-needed">#:neededLabel#</div>'
        },
        {
          field: "type",
          title: "Type",
          width: "8rem",
          attributes: {
            class: "cell--type"
          }
        },
        {
          field: "attrid",
          title: "Attribute identifier",
          attributes: {
            class: "cell--attrid"
          }
        }
      ]
    });
    fitMaskGridToBottom($tree);
    const famidInput = maskHeader.find("input[name='msk_famid_input']");
    famidInput.on("change", e => {
      controller.setValue("msk_famid", { value: e.target.value });
    });
  });
});
