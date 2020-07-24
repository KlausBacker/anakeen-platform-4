import { Component, Prop, Mixins } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";

import "@progress/kendo-ui/js/kendo.grid";

@Component({
  components: {
    "ank-split-panes": AnkPaneSplitter,
    AnkSmartElement: () => AnkSmartElement
  }
})
export default class AdminCenterPreviousTasksController extends Mixins(AnkI18NMixin) {
  @Prop({ type: Boolean, required: true })
  public timerTab!: boolean;
  public displaySMartElement = false;
  public saveRow: any;

  public $refs!: {
    [key: string]: any;
  };
  public kendoGridPreviousAction: any = null;

  // funtion to set the dataSource on the kendoGrid
  public paramGridData: kendo.data.DataSource = new kendo.data.DataSource({
    schema: {
      model: {
        fields: {
          task_exec_date: { type: "date" }
        }
      },
      data: response => {
        return response.data.data.tasks;
      },
      total: response => {
        if (response.data.data) {
          return response.data.data.total;
        } else {
          return 0;
        }
      }
    },
    serverFiltering: true,
    serverPaging: true,
    serverSorting: true,
    transport: {
      read: options => {
        this.$http
          .get("/api/v2/admin/sheduling/past-tasks/", {
            params: options.data,
            paramsSerializer: kendo.jQuery.param
          })
          .then(result => {
            // notify the data source that the request succeeded
            options.success(result);
            // To display number of results : the first time
            $(window).trigger("resize");
          })
          .catch(options.error);
        return options;
      }
    },
    pageSize: 50
  });

  // action click to last column of kendoGrid
  public onClickAction(e) {
    this.displaySMartElement = true;
    const dataItem = this.kendoGridPreviousAction.dataItem($(e.currentTarget).closest("tr"));

    // Put backgroud to know what we selected
    if (this.saveRow) {
      this.saveRow.closest("tr").removeClass("tr-selected");
    }
    this.saveRow = $(e.currentTarget);
    this.saveRow.closest("tr").addClass("tr-selected");

    const openDocPreviousTask = this.$refs.openDocPreviousTask;
    if (openDocPreviousTask) {
      openDocPreviousTask.fetchSmartElement({
        initid: dataItem.id,
        revision: dataItem.revision,
        viewId: "!defaultConsultation"
      });
    }
  }

  public afterSaveRefreshGrid(): void {
    this.kendoGridPreviousAction.dataSource.read();
  }

  public mounted() {
    // @ts-ignore
    this.kendoGridPreviousAction = $(this.$refs.gridPreviousTask)
      .kendoGrid({
        columns: [
          {
            field: "title",
            title: this.$t("AdminCenterPreviousTasksTranslation.Tasks"),
            sortable: false
          },
          {
            field: "task_exec_state_result",
            title: this.$t("AdminCenterPreviousTasksTranslation.Status"),
            filterable: false,
            sortable: false
          },
          {
            field: "task_exec_date",
            title: this.$t("AdminCenterPreviousTasksTranslation.Execution date"),
            filterable: false,
            sortable: false,
            format: "{0:F}"
          },
          {
            field: "task_exec_duration",
            title: this.$t("AdminCenterPreviousTasksTranslation.Duration"),
            filterable: false,
            sortable: false
          },
          {
            command: [
              {
                name: "Detail",
                click: this.onClickAction,
                text: this.$t("AdminCenterPreviousTasksTranslation.Detail")
              }
            ],
            filterable: false
          }
        ],
        dataSource: this.paramGridData,
        pageable: {
          pageSizes: [50, 100, 200],
          refresh: true,
          messages: {
            itemsPerPage: this.$t("AdminCenterKendoGridTranslation.items per page"),
            display: this.$t("AdminCenterPreviousTasksTranslation.{0}-{1}of{2}items"),
            refresh: this.$t("AdminCenterKendoGridTranslation.Refresh"),
            NoData: this.$t("AdminCenterKendoGridTranslation.No data"),
            empty: this.$t("AdminCenterKendoGridTranslation.No data")
          }
        },
        scrollable: true,
        sortable: true,
        filterable: {
          extra: false,
          operators: {
            string: {
              contains: this.$t("AdminCenterKendoGridTranslation.contains")
            }
          },
          messages: {
            info: this.$t("AdminCenterKendoGridTranslation.Filter by") + ": ",
            operator: this.$t("AdminCenterKendoGridTranslation.Choose operator"),
            clear: this.$t("AdminCenterKendoGridTranslation.Clear"),
            filter: this.$t("AdminCenterKendoGridTranslation.Apply"),
            value: this.$t("AdminCenterKendoGridTranslation.Choose value"),
            additionalValue: this.$t("AdminCenterKendoGridTranslation.Aditional value"),
            title: this.$t("AdminCenterKendoGridTranslation.Aditional filter by")
          }
        }
      })
      .data("kendoGrid");
  }
}
