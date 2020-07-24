import { Component, Prop, Mixins } from "vue-property-decorator";
import VueI18n from "vue-i18n";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
// import * as $ from "jquery";
import AnkTabs from "@anakeen/user-interfaces/components/lib/AnkTabs.esm";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab.esm";
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import "@progress/kendo-ui/js/kendo.grid";

const AdminCenterDetailsIncomingTimers = () => import("./IncomingDetails/IncomingDetails.vue");

@Component({
  components: {
    AnkTabs,
    AnkTab,
    "ank-split-panes": AnkPaneSplitter,
    "details-incoming-timer": AdminCenterDetailsIncomingTimers
  }
})
export default class AdminCenterTimersController extends Mixins(AnkI18NMixin) {
  @Prop({ type: Boolean, required: true })
  public timerTab!: boolean;
  public saveRow: any;

  public uriTimer = "";
  public isEmpty = true;
  public $refs!: {
    [key: string]: any;
  };
  public kendoGridIncomingAction: any = null;
  public mySelectedTab = "futurTimerActions";

  public get translations(): { [key: string]: VueI18n.TranslateResult } {
    return {
      futurActions: this.$t("AdminCenterIncomingTranslation.Incoming actions"),
      pasedActions: this.$t("AdminCenterIncomingTranslation.Previous actions")
    };
  }

  // funtion to set the dataSource on the kendoGrid
  public paramGridData: kendo.data.DataSource = new kendo.data.DataSource({
    schema: {
      model: {
        fields: {
          todoDate: { type: "date" }
        }
      },
      data: response => {
        // response.data.data[""]
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
          .get("/api/v2/admin/sheduling/timers/", {
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
    this.uriTimer = this.kendoGridIncomingAction.dataItem($(e.currentTarget).closest("tr")).uri;
    this.isEmpty = false;
    // Put backgroud to know what we selected
    if (this.saveRow) {
      this.saveRow.closest("tr").removeClass("tr-selected");
    }
    this.saveRow = $(e.currentTarget);
    this.saveRow.closest("tr").addClass("tr-selected");
  }

  public mounted() {
    // @ts-ignore
    this.kendoGridIncomingAction = $(this.$refs.gridIncomingTimer)
      .kendoGrid({
        columns: [
          {
            field: "attachTo.title",
            title: this.$t("AdminCenterIncomingTranslation.Title"),
            sortable: false
          },
          {
            field: "attachTo.state",
            title: this.$t("AdminCenterIncomingTranslation.Step"),
            filterable: false,
            sortable: false
          },
          {
            field: "planedActions",
            title: this.$t("AdminCenterIncomingTranslation.Planned actions"),
            template:
              "<ul># for (var i = 0; i < planedActions.length; i++) { #<li>#= planedActions[i] #</li># } #</ul>",
            filterable: false,
            sortable: false
          },
          {
            field: "todoDate",
            title: this.$t("AdminCenterIncomingTranslation.Execution date"),
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
            filterable: false,
            sortable: false
          }
        ],
        dataSource: this.paramGridData,
        pageable: {
          pageSizes: [50, 100, 200],
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
        },
        filterMenuInit(e) {
          $(e.container)
            .find(".k-primary")
            .click(function() {
              const val = $(e.container)
                .find('[title="Value"]')
                .val();
              if (val == "") {
                this.kendoGridIncomingAction.dataSource.filter({});
              }
            });
        }
      })
      .data("kendoGrid");
  }
}
