import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { DropdownsInstaller } from "@progress/kendo-dropdowns-vue-wrapper";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import axios from "axios";
import Vue from "vue";
import Component from "vue-class-component";
import { Prop, Watch } from "vue-property-decorator";

Vue.use(ButtonsInstaller);
Vue.use(DropdownsInstaller);

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    "ank-splitter": AnkSplitter
  }
})
export default class VaultManagerController extends Vue {
  public static convertBytes(x) {
    const units = ["bytes", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
    let l = 0;
    let n = parseInt(x, 10) || 0;
    const neg = n < 0;
    let human;

    n = Math.abs(n);
    while (n >= 1024 && ++l) {
      n = n / 1024;
    }
    human = n.toFixed(n < 10 && l > 0 ? 1 : 0) + " " + units[l];
    if (neg) {
      human = "-" + human;
    }
    return human;
  }
  @Prop({ default: "", type: String }) public value!: string;
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
  public requestMessage: string = "";
  public sizeOptions = [
    {
      text: "kB",
      value: 1024
    },
    {
      text: "MB",
      value: 1024 * 1024
    },
    {
      text: "GB",
      value: 1024 * 1024 * 1024
    }
  ];

  private vaultGrid: any;
  private selectedFs: string = "";

  @Watch("selectedFs")
  public onSelectedFsDataChange(newVal, oldVal) {
    if (newVal !== oldVal) {
      this.$emit("input", newVal);
    }
  }

  @Watch("value")
  public onValuePropChanged(newVal, oldVal) {
    if (newVal !== oldVal) {
      this.selectedFs = newVal;
    }
  }

  public refreshVaultGrid() {
    this.vaultsGridData.read();
  }

  // noinspection JSMethodCanBeStatic
  public closeWindow(e) {
    e.sender.element
      .closest("[data-role=window]")
      .data("kendoWindow")
      .close();
  }
  public requestCreateIt(e) {
    this.closeWindow(e);

    const newSize: string = $(this.$refs.newSize).val() as string;
    const kSizeUnit: any = this.$refs.kNewSizeUnit as any;
    axios
      .post("/api/v2/admin/vaults/", {
        path: $(this.$refs.newPath).val(),
        size: Math.floor(
          parseFloat(newSize) * parseFloat(kSizeUnit.kendoWidget().value())
        )
      })
      .then(response => {
        const data = response.data.data;

        if (data && response.data.messages) {
          this.requestMessage = response.data.messages[0].contentText;
        }
        $(this.$refs.infoUpdate)
          .kendoWindow({
            actions: ["Close"],
            close: () => {
              this.refreshVaultGrid();
            },
            modal: true,
            title: "Vault updated",
            visible: false
          })
          .data("kendoWindow")
          .center()
          .open();
      })
      .catch(info => {
        if (info.response && info.response.data && info.response.data.error) {
          window.alert(info.response.data.error);
        } else if (
          info.response &&
          info.response.data &&
          info.response.data.message
        ) {
          window.alert(info.response.data.message);
        } else {
          window.alert("Fail update vault, see console for more details");
          console.error("reject response", info);
        }
      });
  }

  public onCreateVault() {
    $(this.$refs.createVaultForm)
      .kendoWindow({
        actions: ["Close"],
        modal: true,
        title: "Create vault",
        visible: false
      })
      .data("kendoWindow")
      .center()
      .open();
  }

  public mounted() {
    this.vaultGrid = $(this.$refs.vaultManagerGrid)
      .kendoGrid({
        columns: [
          {
            field: "path",
            template: dataItem => {
              let vaultErrorClass = "";
              if (
                dataItem.disk.totalSize === 0 ||
                dataItem.metrics.totalSize < dataItem.metrics.usedSize
              ) {
                vaultErrorClass = " vault-grid--error";
              }
              return (
                dataItem.path +
                "<div class='vault-grid-sizes" +
                vaultErrorClass +
                "'> (" +
                VaultManagerController.convertBytes(
                  dataItem.metrics.totalSize - dataItem.metrics.usedSize
                ) +
                " / " +
                VaultManagerController.convertBytes(
                  dataItem.metrics.totalSize
                ) +
                ") </div>"
              );
            },
            title: "Vault"
          },
          {
            command: {
              click: e => {
                e.preventDefault();
                Vue.component("ank-vault-info", resolve => {
                  import("./VaultInfo/VaultInfo.vue").then(AnkVaultInfo => {
                    resolve(AnkVaultInfo.default);
                  });
                });
                const $tr = $(e.currentTarget).closest("tr");
                const dataItem = this.vaultGrid.dataItem($tr);
                // @ts-ignore
                this.info = dataItem.toJSON();
                // @ts-ignore
                this.$refs.vaultSplitter.disableEmptyContent();
                this.selectedFs = dataItem.fsid;
                $tr
                  .closest("tbody")
                  .find("tr")
                  .removeClass("vault--selected");
                $tr.addClass("vault--selected");
              },
              text: "Info"
            },
            width: "10rem"
          }
        ],

        dataBound: e => {
          const grid = e.sender;
          this.addRowClassName(grid);

          if (!this.selectedFs) {
            const $viewButtons = $(".k-button.k-grid-Info", this.$el);
            // view first vault
            $($viewButtons.get(0)).trigger("click");
          } else {
            const $viewButtons = $(this.$el).find(
              "tr[data-fsid=" + this.selectedFs + "] .k-button.k-grid-Info"
            );
            $($viewButtons.get(0)).trigger("click");
          }
        },

        dataSource: this.vaultsGridData
      })
      .data("kendoGrid");
    if (this.value) {
      this.selectedFs = this.value;
    }
  }
  /**
   * add token in tr tag to easily select tr
   * @param grid
   */
  protected addRowClassName(grid) {
    const items = grid.items();

    items.each(function addTypeClass(this: any) {
      const dataItem = grid.dataItem(this);
      if (dataItem.fsid) {
        $(this).attr("data-fsid", dataItem.fsid);
      }
    });
  }
}