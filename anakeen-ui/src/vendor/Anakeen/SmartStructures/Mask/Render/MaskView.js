import "./MaskView.css";
import $ from "jquery";
import "@progress/kendo-ui/js/kendo.treelist";

{
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

  window.dcp.document.documentController(
    "addEventListener",
    "ready",
    {
      name: "maskReady",
      documentCheck: documentObject =>
        documentObject.renderMode === "view" &&
        documentObject.family.name === "MASK"
    },
    function maskViewReady(event, documentObject) {
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
              url: "api/v2/admin/mask/" + documentObject.id + "/visibilities/"
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
              '<div class="mask-visibility mask-visibility-set">#:mVisibilityLabel#</div>' +
              "# } else if (data.setVisibility === false && data.visibility !== data.mVisibility)" +
              ' {#<div class="mask-visibility mask-visibility-modified">#:mVisibilityLabel#</div>#}' +
              'else if (data.mVisibility) {# <div class="mask-visibility">#:mVisibilityLabel#</div>  #}#'
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
              '<div class="mask-needed mask-needed-set">#:mNeededLabel#</div>' +
              '# } else if (data.needed !== undefined) {# <div class="mask-needed">#:mNeededLabel#</div>' +
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
    }
  );
}
