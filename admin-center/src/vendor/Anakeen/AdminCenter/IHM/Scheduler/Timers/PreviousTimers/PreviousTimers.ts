import { Component, Prop, Mixins } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import "@progress/kendo-ui/js/kendo.grid";

const AdminCenterDetailsPreviousTimers = () => import("./PreviousDetails/PreviousDetails.vue");

@Component({
  components: {
    "ank-split-panes": AnkPaneSplitter,
    "details-previous-timer": AdminCenterDetailsPreviousTimers
  }
})
export default class AdminCenterTimersController extends Mixins(AnkI18NMixin) {
  @Prop({ type: Boolean, required: true })
  public timerTab!: boolean;
  public isEmpty = true;
  public uriTimer = "";
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
          expectedDate: { type: "date" }
        }
      },
      data: response => {
        return response.data.data.results;
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
          .get("/api/v2/admin/sheduling/past-timers/", {
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
    this.isEmpty = false;
    this.uriTimer = this.kendoGridPreviousAction.dataItem($(e.currentTarget).closest("tr")).uri;
    // Put backgroud to know what we selected
    if (this.saveRow) {
      this.saveRow.closest("tr").removeClass("tr-selected");
    }
    this.saveRow = $(e.currentTarget);
    this.saveRow.closest("tr").addClass("tr-selected");
  }

  public mounted() {
    // @ts-ignore
    this.kendoGridPreviousAction = $(this.$refs.gridPreviousTimer)
      .kendoGrid({
        columns: [
          {
            field: "attachTo.title",
            title: this.$t("AdminCenterPreviousTranslation.Title"),
            sortable: false
          },
          {
            field: "attachTo.state",
            title: this.$t("AdminCenterPreviousTranslation.Step"),
            filterable: false,
            sortable: false
          },
          {
            field: "planedActions",
            title: this.$t("AdminCenterPreviousTranslation.Executed actions"),
            template:
              "<ul># for (var i = 0; i < planedActions.length; i++) { #<li>#= planedActions[i] #</li># } #</ul>",
            filterable: false,
            sortable: false
          },
          {
            field: "status",
            title: this.$t("AdminCenterPreviousTranslation.Status"),
            filterable: false,
            sortable: false
          },
          {
            field: "expectedDate",
            title: this.$t("AdminCenterPreviousTranslation.Execution date"),
            filterable: false,
            sortable: false,
            format: "{0:F}"
          },
          {
            command: [
              {
                name: "Detail",
                click: this.onClickAction,
                text: this.$t("AdminCenterTimers.Detail")
              }
            ],
            filterable: false
          }
        ],
        dataSource: this.paramGridData,
        pageable: {
          pageSizes: [50, 100, 200],
          info: true,
          refresh: true,
          messages: {
            itemsPerPage: this.$t("AdminCenterKendoGridTranslation.items per page"),
            display: this.$t("AdminCenterTimersTranslation.{0}-{1}of{2}items"),
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
