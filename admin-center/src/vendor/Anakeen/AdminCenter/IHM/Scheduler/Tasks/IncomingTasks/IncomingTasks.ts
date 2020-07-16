import { Component, Prop, Mixins } from "vue-property-decorator";
import VueI18n from "vue-i18n";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
// import * as $ from "jquery";
import AnkTabs from "@anakeen/user-interfaces/components/lib/AnkTabs.esm";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab.esm";
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import "@progress/kendo-ui/js/kendo.grid";

@Component({
  components: {
    AnkTabs,
    AnkTab,
    AnkSmartElement: () => AnkSmartElement,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterIncomingTasksController extends Mixins(AnkI18NMixin) {
  @Prop({ type: Boolean, required: true })
  public timerTab!: boolean;
  public displaySMartElement = false;
  public saveRow: any;

  public $refs!: {
    [key: string]: any;
  };
  public kendoGridIncomingAction: any = null;

  public get translations(): { [key: string]: VueI18n.TranslateResult } {
    return {
      IncomingActions: this.$t("AdminCenterPreviousTasksTranslation.Incoming actions"),
      previousActions: this.$t("AdminCenterPreviousTasksTranslation.Previous actions")
    };
  }

  // funtion to set the dataSource on the kendoGrid
  public paramGridData: kendo.data.DataSource = new kendo.data.DataSource({
    schema: {
      data: "data.data.tasks",
      model: {
        fields: {
          title: { type: "string" },
          name: { type: "string" },
          status: { type: "string" },
          task_nextdate: { type: "date" }
        }
      },
      total: "data.data.total"
    },
    serverFiltering: true,
    serverPaging: true,
    serverSorting: true,
    transport: {
      read: options => {
        this.$http
          .get("/api/v2/admin/sheduling/tasks/", {
            params: options.data,
            paramsSerializer: kendo.jQuery.param
          })
          .then(result => {
            // notify the data source that the request succeeded
            options.success(result);
            // To display number of results : the first time
            $(window).trigger("resize");
          })
          .catch(error => {
            options.error(error);
          });
        return options;
      }
    },
    pageSize: 50
  });

  // action click to last column of kendoGrid
  public onClickAction(e) {
    this.displaySMartElement = true;
    const dataItem = this.kendoGridIncomingAction.dataItem($(e.currentTarget).closest("tr"));
    // Put backgroud to know what we selected
    if (this.saveRow) {
      this.saveRow.closest("tr").removeClass("tr-selected");
    }
    this.saveRow = $(e.currentTarget);
    this.saveRow.closest("tr").addClass("tr-selected");

    const openDocIncomingTask = this.$refs.openDocIncomingTask;
    if (openDocIncomingTask) {
      openDocIncomingTask.fetchSmartElement({
        initid: dataItem.id,
        viewId: "!defaultConsultation"
      });
    }
  }

  public afterSaveRefreshGrid(): void {
    this.kendoGridIncomingAction.dataSource.read();
  }

  public mounted() {
    // @ts-ignore
    this.kendoGridIncomingAction = $(this.$refs.gridIncomingTasks)
      .kendoGrid({
        columns: [
          {
            field: "title",
            title: this.$t("AdminCenterPreviousTasksTranslation.Tasks"),
            sortable: false
          },
          {
            field: "task_status",
            title: this.$t("AdminCenterPreviousTasksTranslation.Status"),
            filterable: false,
            sortable: false
          },
          {
            field: "task_nextdate",
            title: this.$t("AdminCenterPreviousTasksTranslation.Next execution date"),
            filterable: false,
            sortable: false,
            format: "{0:F}"
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
