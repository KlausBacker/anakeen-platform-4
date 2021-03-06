import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.grid";
import { Component, Mixins } from "vue-property-decorator";
import TeTaskInfo from "./TeTaskInfo.vue";
import { debounced } from "./TeUtils";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    "ank-splitter": AnkSplitter,
    "te-task-info": TeTaskInfo
  },
  name: "TeSupervision"
})
export default class TeSupervision extends Mixins(AnkI18NMixin) {
  public $refs!: {
    [key: string]: any;
  };
  public selectedTask = "";
  public tePanes: object[] = [
    {
      collapsible: true,
      resizable: true,
      scrollable: false,
      size: "70%"
    },
    {
      collapsible: true,
      resizable: true,
      scrollable: false,
      size: "30%"
    }
  ];

  protected kSupervisionGrid: any;

  public refreshGrid() {
    this.selectedTask = "";

    this.$refs.teSplitter.enableEmptyContent();
    const kendoGrid = $(this.$refs.teSupervisionGrid).data("kendoGrid");
    if (kendoGrid) {
      kendoGrid.dataSource.read();
    }
  }

  public resizeGrid(): void {
    const kendoGrid = $(this.$refs.teSupervisionGrid).data("kendoGrid");
    if (!kendoGrid || !kendoGrid.element) {
      return;
    }
    const $grid = kendoGrid.element;
    const $box = $grid.closest(".te-grid-box");

    $grid.css("height", $box.height());
    window.setTimeout(() => {
      if (kendoGrid) {
        kendoGrid.resize();
      }
    }, 100);
  }
  public initGrid(): void {
    const $grid = $(this.$refs.teSupervisionGrid);
    if ($grid.data("kendoGrid")) {
      return;
    }
    $grid
      .kendoGrid({
        autoBind: true,
        columns: [
          {
            attributes: {
              class: "cell--date"
            },
            field: "cdate",
            filterable: {
              cell: {
                delay: 9999,
                operator: "contains",
                showOperators: false,
                suggestionOperator: "contains"
              }
            },
            template: item => {
              return item.cdate.substr(0, 19);
            },
            title: `${this.$t("AdminCenterTransformationFileManager.Request Date")}`,
            width: "13rem"
          },
          {
            attributes: {
              class: "cell--status"
            },
            field: "status",
            filterable: {
              cell: {
                delay: 9999,
                operator: "contains",
                showOperators: false,
                suggestionOperator: "contains"
              }
            },
            template: item => {
              const callError = item.callreturn && item.callreturn.substr(0, 5) === "ERROR";
              return $("<span/>")
                .text(item.status)
                .addClass(
                  "cell-status-value cell-status--" + item.status + (callError ? " cell-status--CALLERROR" : "")
                )
                .get(0).outerHTML;
            },
            title: `${this.$t("AdminCenterTransformationFileManager.Status")}`
          },
          {
            attributes: {
              class: "cell--engine"
            },
            field: "engine",
            filterable: {
              cell: {
                delay: 1,
                operator: "contains",
                showOperators: false,
                suggestionOperator: "contains"
              }
            },
            title: `${this.$t("AdminCenterTransformationFileManager.Engine")}`
          },
          {
            attributes: {
              class: "cell--id"
            },
            field: "id",
            filterable: {
              cell: {
                delay: 9999,
                operator: "contains",
                showOperators: false,
                suggestionOperator: "contains"
              }
            },

            title: `${this.$t("AdminCenterTransformationFileManager.Task Id")}`
          },
          {
            command: {
              click: e => {
                // Hello
                this.$refs.teSplitter.disableEmptyContent();
                const $tr = $(e.currentTarget).closest("tr");
                // @ts-ignore
                this.selectedTask = $(this.$refs.teSupervisionGrid)
                  .data("kendoGrid")
                  .dataItem($tr)
                  .toJSON();

                $tr
                  .closest("tbody")
                  .find("tr")
                  .removeClass("task--selected");
                $tr.addClass("task--selected");
              },
              text: "Info"
            },

            width: "10rem"
          }
        ],
        dataBound: () => {
          window.setTimeout(() => {
            $(window).trigger("resize");
          }, 1);
        },
        dataSource: {
          pageSize: 25,
          schema: {
            data: response => {
              return response.data.data.tasks;
            },
            model: {
              id: "tid"
            },
            total: "data.data.total"
          },

          serverFiltering: true,
          serverPaging: true,
          serverSorting: true,
          transport: {
            read: options => {
              this.$http
                .get(`/api/admin/transformationengine/tasks/`, {
                  params: options.data
                })
                .then(result => {
                  // notify the data source that the request succeeded
                  options.success(result);
                })
                .catch(result => {
                  // notify the data source that the request failed
                  options.error(result);
                });
            }
          }
        },
        filterable: {
          extra: false,
          mode: "row"
        },
        pageable: {
          buttonCount: 5,
          pageSizes: [25, 100],
          refresh: true,
          messages: {
            itemsPerPage: `${this.$t("AdminCenterKendoGridTranslation.items per page")}`,
            display: `${this.$t("AdminCenterKendoGridTranslation.{0}-{1}of{2}items")}`,
            refresh: `${this.$t("AdminCenterKendoGridTranslation.Refresh")}`,
            empty: `${this.$t("AdminCenterKendoGridTranslation.No data")}`
          }
        }
      })
      .data("kendoGrid");
  }

  public beforeDestroy() {
    $(window).off("resize.tesuper");
  }

  public mounted() {
    this.initGrid();
    $(window).on(
      "resize.tesuper",
      debounced(100, () => {
        this.resizeGrid();
      })
    );
  }

  public updated() {
    this.initGrid();
  }
}
