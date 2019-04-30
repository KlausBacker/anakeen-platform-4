import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { DropdownsInstaller } from "@progress/kendo-dropdowns-vue-wrapper";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import Vue from "vue";
import Component from "vue-class-component";
import { Prop, Watch } from "vue-property-decorator";

Vue.use(ButtonsInstaller);
Vue.use(DropdownsInstaller);

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    "ank-splitter": AnkSplitter,
    "ank-vault-info": () =>
      new Promise(resolve => {
        import("./VaultInfo/VaultInfo.vue").then(AnkVaultInfo => {
          resolve(AnkVaultInfo.default);
        });
      })
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
  @Prop({ default: "", type: String })
  public value!: string;
  public info: any = {};
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
      this.selectFsRow(this.selectedFs);
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
    this.$http
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

  public updated() {
    this.initKendoGrid();
  }

  public mounted() {
    this.initKendoGrid();
    if (this.value) {
      this.selectedFs = this.value;
    }
  }

  protected selectFsRow(fsId) {
    const $viewButtons = $(this.$el).find(
      "tr[data-fsid=" + fsId + "] .k-button.k-grid-Info"
    );
    $($viewButtons.get(0)).trigger("click");
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

  protected initKendoGrid() {
    if (!$(this.$refs.vaultManagerGrid).data("kendoGrid")) {
      $(this.$refs.vaultManagerGrid)
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
                    dataItem.metrics.usedSize
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
                  const $tr = $(e.currentTarget).closest("tr");
                  const kendoGrid = $(this.$refs.vaultManagerGrid).data(
                    "kendoGrid"
                  );
                  const dataItem = kendoGrid.dataItem($tr);
                  // @ts-ignore
                  this.info = dataItem.toJSON();
                  // @ts-ignore
                  this.$refs.vaultSplitter.disableEmptyContent();
                  // @ts-ignore
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
              // view first vault
              // @ts-ignore
              this.selectFsRow(grid.dataSource.data().at(0).fsid);
            } else {
              this.selectFsRow(this.selectedFs);
            }
          },

          dataSource: this.vaultsGridData
        })
        .data("kendoGrid");
    }
  }
}
