// import Splitter from "../Components/Splitter/Splitter.vue";
import { AnkSplitter } from "@anakeen/internal-components";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import Vue from "vue";
import Component from "vue-class-component";
import AnkVaultInfo from "./VaultInfo/VaultInfo.vue";

Vue.use(ButtonsInstaller);
Vue.use(DataSourceInstaller);
Vue.use(GridInstaller);

@Component({
  components: {
    "ank-splitter": AnkSplitter,
    "ank-vault-info": AnkVaultInfo
  }
})
export default class VaultManagerController extends Vue {
  public $refs!: {
    [key: string]: any;
  };
  public info: any = [];
  public panes: object[] = [
    {
      collapsible: true,
      resizable: true,
      scrollable: false,
      size: "30%"
    },
    {
      collapsible: true,
      resizable: true,
      scrollable: false,
      size: "70%"
    }
  ];
  public vaultsGridData: kendo.data.DataSource = new kendo.data.DataSource({
    schema: {
      data: response => response.data.data
    },
    transport: {
      read: options => {
        this.$http
          .get("api/v2/admin/vaults/")
          .then(options.success)
          .catch(options.error);
      }
    }
  });

  public mounted() {
    // @ts-ignore
    $(".vault-manager-grid")
      .kendoGrid({
        columns: [
          {
            field: "path",
            title: "Vault"
          },
          {
            field: "humanMetrics.totalSize",
            title: "Logical capacity",
            width: "10rem"
          },
          {
            field: "freespace",
            title: "Free Space",
            width: "10rem"
          },
          {
            filterable: false,
            // Add a button only if the parameter is modifiable
            template: `<a class="consult-btn" title="Consult">Consult</a>`,
            width: "10rem"
          }
        ],
        toolbar: [
          {
            iconClass: "fa fa-plus",
            name: "create",
            text: "Create"
          }
        ],

        dataBound: e => {
          $(".consult-btn", this.$el).kendoButton();
        },
        dataSource: this.vaultsGridData
      })
      .on("click", ".consult-btn", e => {
        const grid = $(".vault-manager-grid").data("kendoGrid");
        const dataItem = grid.dataItem(e.currentTarget.closest("tr"));
        // @ts-ignore
        this.info = dataItem.toJSON();
        // @ts-ignore
        this.info.series = [
          {
            data: [
              {
                category: "Referenced",
                color: "#17a2b8",
                nbFiles: this.info.metrics.repartition.usefulCount,
                sizeFiles: this.info.metrics.repartition.usefulSize,
                value: Math.floor(
                  (this.info.metrics.repartition.usefulSize /
                    this.info.metrics.totalSize) *
                    100
                )
              },
              {
                category: "Trash can",
                color: "#dc3545",
                nbFiles: this.info.metrics.repartition.trashCount,
                sizeFiles: this.info.metrics.repartition.trashSize,
                value: Math.floor(
                  (this.info.metrics.repartition.trashSize /
                    this.info.metrics.totalSize) *
                    100
                )
              },
              {
                category: "Orphans",
                color: "#ffc107",
                nbFiles: this.info.metrics.repartition.orphanCount,
                sizeFiles: this.info.metrics.repartition.orphanSize,
                value: Math.floor(
                  (this.info.metrics.repartition.orphanSize /
                    this.info.metrics.totalSize) *
                    100
                )
              }
            ],
            type: "pie"
          }
        ];
        this.$refs.vaultSplitter.disableEmptyContent();
      });
  }
}
