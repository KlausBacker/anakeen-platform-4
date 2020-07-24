import { Component, Prop, Mixins, Watch } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import "@progress/kendo-ui/js/kendo.grid";

@Component({
  components: {}
})
export default class AdminCenterPreviousDetailsController extends Mixins(AnkI18NMixin) {
  @Prop({ type: String, required: true })
  public uriTimer!: string;

  public info = {};

  public $refs!: {
    [key: string]: any;
  };

  public kendoGridPreviousDetails: any = null;

  @Watch("uriTimer")
  public updateUri(): void {
    this.kendoGridPreviousDetails.dataSource.read();
  }

  public setInfoDetail(info): void {
    this.info = info;
    this.info["formatAttachDate"] = kendo.toString(new Date(info.attachdate), "F");
  }

  // funtion to set the dataSource on the kendoGrid
  public GridData: kendo.data.DataSource = new kendo.data.DataSource({
    schema: {
      model: {
        fields: {
          date: { type: "date" }
        }
      },
      data: response => {
        const dataRequest = response.data.data;
        this.setInfoDetail(dataRequest.taskInfo);
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

  public mounted() {
    // @ts-ignore
    this.kendoGridPreviousDetails = $(this.$refs.gridPreviousTimerDetail)
      .kendoGrid({
        columns: [
          {
            field: "date",
            title: this.$t("AdminCenterPreviousDetailsTranslation.Planned date"),
            filterable: false,
            sortable: false,
            format: "{0:F}"
          },
          {
            field: "message",
            title: this.$t("AdminCenterPreviousDetailsTranslation.Action"),
            filterable: false,
            sortable: false
          }
        ],
        dataSource: this.GridData,
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
