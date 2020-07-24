import { Component, Prop, Mixins, Watch } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
// import * as $ from "jquery";
import "@progress/kendo-ui/js/kendo.grid";

@Component({
  components: {}
})
export default class AdminCenterIncomingDetailsController extends Mixins(AnkI18NMixin) {
  @Prop({ type: String, required: true })
  public uriTimer!: string;
  public infos = {};

  public $refs!: {
    [key: string]: any;
  };
  // public kendoGridIncomingAction: any = null;
  public kendoGridIncomingDetails: any = null;

  public setInfosDetail(infos): void {
    this.infos = infos;
  }

  // funtion to set the dataSource on the kendoGrid
  public paramGridData: kendo.data.DataSource = new kendo.data.DataSource({
    schema: {
      model: {
        fields: {
          date: { type: "date" }
        }
      },
      data: response => {
        const dataRequest = response.data.data;
        this.setInfosDetail(dataRequest.taskInfo);
        return dataRequest.actions;
      },
      total: response => {
        return response.data.data.actions.length;
      }
    },
    serverFiltering: true,
    serverPaging: true,
    serverSorting: true,
    transport: {
      read: options => {
        this.$http
          .get(this.uriTimer, {
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
    }
  });

  @Watch("uriTimer")
  public updateUri(): void {
    this.kendoGridIncomingDetails.dataSource.read();
  }

  public mounted(): void {
    // @ts-ignore
    this.kendoGridIncomingDetails = $(this.$refs.gridIncomingTimerDetail)
      .kendoGrid({
        columns: [
          {
            field: "date",
            title: this.$t("AdminCenterIncomingDetailsTranslation.Execution date"),
            filterable: false,
            sortable: false,
            format: "{0:F}"
          },
          {
            field: "message",
            title: this.$t("AdminCenterIncomingDetailsTranslation.Action"),
            filterable: false,
            sortable: false
          }
        ],
        dataSource: this.paramGridData,
        pageable: {
          input: false,
          numeric: false,
          pageSizes: false,
          previousNext: false,
          refresh: true,
          messages: {
            itemsPerPage: this.$t("AdminCenterKendoGridTranslation.items per page"),
            display: this.$t("AdminCenterTimersDetailTranslation.nothing"),
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
