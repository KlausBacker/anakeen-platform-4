// @ts-ignore
import { Chart, ChartInstaller } from "@progress/kendo-charts-vue-wrapper";
import { DropdownsInstaller } from "@progress/kendo-dropdowns-vue-wrapper";
import { GaugesInstaller } from "@progress/kendo-gauges-vue-wrapper";
import "@progress/kendo-ui/js/kendo.dataviz";
import axios from "axios";
import { Component, Prop, Vue } from "vue-property-decorator";

Vue.use(ChartInstaller);
Vue.use(GaugesInstaller);
Vue.use(DropdownsInstaller);

// noinspection JSUnusedGlobalSymbols
@Component({
  name: "ank-vault-info"
})
export default class VaultInfoController extends Vue {
  @Prop({ type: Object as () => {} }) public info;

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

  // noinspection JSMethodCanBeStatic
  public convertBytes(x) {
    const units = ["bytes", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
    let l = 0;
    let n = parseInt(x, 10) || 0;
    while (n >= 1024 && ++l) {
      n = n / 1024;
    }
    return n.toFixed(n < 10 && l > 0 ? 1 : 0) + " " + units[l];
  }

  public labelTemplate(data) {
    if (data.category === "Free") {
      return `${data.category} - ${this.convertBytes(
        data.dataItem.sizeFiles
      )} (${data.dataItem.value}%)`;
    }
    return `${data.category} - ${
      data.dataItem.nbFiles
    } files \n ${this.convertBytes(data.dataItem.sizeFiles)} (${
      data.dataItem.value
    }%)`;
  }

  public get getSeries() {
    let freeSize;
    let orphanPc;
    let referencedPc;
    let trashPc;
    const totalSize = Math.max(
      this.info.metrics.totalSize,
      this.info.metrics.usedSize
    );

    orphanPc = Math.ceil(
      (this.info.metrics.repartition.orphanSize / totalSize) * 100
    );
    trashPc = Math.ceil(
      (this.info.metrics.repartition.trashSize / totalSize) * 100
    );

    referencedPc = Math.ceil(
      (this.info.metrics.repartition.usefulSize / totalSize) * 100
    );

    freeSize = Math.max(100 - orphanPc - trashPc - referencedPc, 0);
    return [
      {
        data: [
          {
            category: "Referenced",
            color: "#17a2b8",
            nbFiles: this.info.metrics.repartition.usefulCount,
            sizeFiles: this.info.metrics.repartition.usefulSize,
            value: referencedPc
          },
          {
            category: "Trash can",
            color: "#dc5c8c",
            nbFiles: this.info.metrics.repartition.trashCount,
            sizeFiles: this.info.metrics.repartition.trashSize,
            value: trashPc
          },
          {
            category: "Orphans",
            color: "#ffc107",
            nbFiles: this.info.metrics.repartition.orphanCount,
            sizeFiles: this.info.metrics.repartition.orphanSize,
            value: orphanPc
          },
          {
            category: "Free",
            color: "#28a644",
            nbFiles: 0,
            sizeFiles: totalSize - this.info.metrics.usedSize,
            value: freeSize
          }
        ],
        type: "pie"
      }
    ];
  }

  public logicalTemplate() {
    return `<b>${this.convertBytes(
      this.info.metrics.usedSize
    )}</b> used on <b>${this.convertBytes(
      this.info.metrics.totalSize
    )}</b> (<b>${Math.floor(
      (this.info.metrics.usedSize / this.info.metrics.totalSize) * 100
    )}%</b>)`;
  }

  public diskTemplate() {
    return `<b>${this.convertBytes(
      this.info.disk.usedSize
    )}</b> used on <b>${this.convertBytes(
      this.info.disk.totalSize
    )}</b> (<b>${Math.floor(
      (this.info.disk.usedSize / this.info.disk.totalSize) * 100
    )}%</b>)`;
  }

  // noinspection JSMethodCanBeStatic
  public closeWindow(e) {
    e.sender.element
      .closest("[data-role=window]")
      .data("kendoWindow")
      .close();
  }

  public requestMoveIt(e) {
    this.closeWindow(e);
    axios
      .put(
        "/api/v2/admin/vaults/" + this.info.fsid + "/path/",
        $(this.$refs.newPath).val(),
        {
          headers: { "Content-Type": "text/plain" }
        }
      )
      .then(response => {
        const data = response.data.data;

        if (data && response.data.messages) {
          this.requestMessage = response.data.messages[0].contentText;
        }
        $(this.$refs.infoUpdate)
          .kendoWindow({
            actions: ["Close"],
            close: () => {
              this.$emit("vault-updated", data);
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

  public requestResizeIt(e) {
    this.closeWindow(e);
    const newSize: string = $(this.$refs.newSize).val() as string;
    const kSizeUnit: any = this.$refs.kNewSizeUnit as any;
    axios
      .put(
        "/api/v2/admin/vaults/" + this.info.fsid + "/size/",
        Math.floor(
          parseFloat(newSize) * parseFloat(kSizeUnit.kendoWidget().value())
        ),
        {
          headers: { "Content-Type": "text/plain" }
        }
      )
      .then(response => {
        const data = response.data.data;

        if (data && response.data.messages) {
          this.requestMessage = response.data.messages[0].contentText;
        }
        $(this.$refs.infoUpdate)
          .kendoWindow({
            actions: ["Close"],
            close: () => {
              this.$emit("vault-updated", data);
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

  public onMovePath() {
    $(this.$refs.movePathForm)
      .kendoWindow({
        actions: ["Close"],
        modal: true,
        title: "Move path",
        visible: false
      })
      .data("kendoWindow")
      .center()
      .open();
  }

  public onResizeDisk() {
    $(this.$refs.resizeVolumeForm)
      .kendoWindow({
        actions: ["Close"],
        modal: true,
        title: "Resize logical volume",
        visible: false
      })
      .data("kendoWindow")
      .center()
      .open();
  }

  // public mounted() {}
}
