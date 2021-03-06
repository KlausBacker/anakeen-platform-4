import { ChartInstaller } from "@progress/kendo-charts-vue-wrapper";
import { DropdownsInstaller } from "@progress/kendo-dropdowns-vue-wrapper";
import { GaugesInstaller } from "@progress/kendo-gauges-vue-wrapper";
import "@progress/kendo-ui/js/dataviz/chart/chart";
import "@progress/kendo-ui/js/dataviz/gauge/main";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import { Component, Prop, Vue, Mixins } from "vue-property-decorator";
import VaultManagerController from "../VaultManager.controller";

Vue.use(ChartInstaller);
Vue.use(GaugesInstaller);
Vue.use(DropdownsInstaller);

// noinspection JSUnusedGlobalSymbols
@Component({
  name: "ank-vault-info"
})
export default class VaultInfoController extends Mixins(AnkI18NMixin) {
  protected static getGaugeUnit(size) {
    const hasta = 2;
    if (size < hasta * 1024 * 1024) {
      return 1024; // kB
    }
    if (size < hasta * 1024 * 1024 * 1024) {
      return 1024 * 1024; // MB
    }
    if (size < hasta * 1024 * 1024 * 1024 * 1024) {
      return 1024 * 1024 * 1024; // GB
    }
    if (size < hasta * 1024 * 1024 * 1024 * 1024 * 1024) {
      return 1024 * 1024 * 1024 * 1024; // TB
    }
  }

  protected static getGaugeRangeLabel(size) {
    switch (VaultInfoController.getGaugeUnit(size)) {
      case 1024:
        return "#.# kB";
      case 1024 * 1024:
        return "#.# MB";
      case 1024 * 1024 * 1024:
        return "#.# GB";
      case 1024 * 1024 * 1024 * 1024:
        return "#.# TB";
    }
    return "#";
  }
  @Prop({ type: Object as () => { metrics: {} } })
  public info;

  public get translations() {
    return {
      NewPath: this.$t("AdminCenterVaultManager.New path"),
      NewSize: this.$t("AdminCenterVaultManager.New size")
    };
  }

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
    return VaultManagerController.convertBytes(x);
  }

  public labelTemplate(data) {
    if (data.category === "Free") {
      return `${data.category} - ${this.convertBytes(data.dataItem.sizeFiles)} (${data.dataItem.value}%)`;
    }
    return `${data.category} - ${data.dataItem.nbFiles} ${this.$t(
      "AdminCenterVaultManager.files"
    )} \n ${this.convertBytes(data.dataItem.sizeFiles)} (${data.dataItem.value}%)`;
  }

  public get getGaugeLogicalMax() {
    return this.info.metrics.totalSize / VaultInfoController.getGaugeUnit(this.info.metrics.totalSize);
  }
  public get getGaugeLogicalUsed() {
    return this.info.metrics.usedSize / VaultInfoController.getGaugeUnit(this.info.metrics.totalSize);
  }

  public get getGaugeLogicalRangeLabel() {
    return VaultInfoController.getGaugeRangeLabel(this.info.metrics.totalSize);
  }

  public get getGaugeLogicalRanges() {
    return [
      {
        color: "#ffc700",
        from: this.getGaugeLogicalMax * 0.7,
        to: this.getGaugeLogicalMax * 0.8
      },
      {
        color: "#ff7a00",
        from: this.getGaugeLogicalMax * 0.8,
        to: this.getGaugeLogicalMax * 0.9
      },
      {
        color: "#c20000",
        from: this.getGaugeLogicalMax * 0.9,
        to: this.getGaugeLogicalMax
      }
    ];
  }

  public get getGaugeDiskMax() {
    return this.info.disk.totalSize / VaultInfoController.getGaugeUnit(this.info.disk.totalSize);
  }
  public get getGaugeDiskUsed() {
    return this.info.disk.usedSize / VaultInfoController.getGaugeUnit(this.info.disk.totalSize);
  }

  public get getGaugeDiskRangeLabel() {
    return VaultInfoController.getGaugeRangeLabel(this.info.disk.totalSize);
  }

  public get getGaugeDiskRanges() {
    return [
      {
        color: "#ffc700",
        from: this.getGaugeDiskMax * 0.7,
        to: this.getGaugeDiskMax * 0.8
      },
      {
        color: "#ff7a00",
        from: this.getGaugeDiskMax * 0.8,
        to: this.getGaugeDiskMax * 0.9
      },
      {
        color: "#c20000",
        from: this.getGaugeDiskMax * 0.9,
        to: this.getGaugeDiskMax
      }
    ];
  }

  public get getSeries() {
    let freeSize;
    let orphanPc;
    let referencedPc;
    let trashPc;
    const data = [];
    if (this.info.metrics && this.info.metrics.totalSize) {
      const totalSize = Math.max(this.info.metrics.totalSize, this.info.metrics.usedSize);

      orphanPc = Math.ceil((this.info.metrics.repartition.orphanSize / totalSize) * 100);
      trashPc = Math.ceil((this.info.metrics.repartition.trashSize / totalSize) * 100);

      referencedPc = Math.ceil((this.info.metrics.repartition.usefulSize / totalSize) * 100);

      freeSize = Math.max(100 - orphanPc - trashPc - referencedPc, 0);
      if (referencedPc) {
        data.push({
          category: this.$t("AdminCenterVaultManager.Referenced"),
          color: "#17a2b8",
          nbFiles: this.info.metrics.repartition.usefulCount,
          sizeFiles: this.info.metrics.repartition.usefulSize,
          value: referencedPc
        });
      }

      if (trashPc > 0) {
        data.push({
          category: this.$t("AdminCenterVaultManager.Trash can"),
          color: "#dc5c8c",
          nbFiles: this.info.metrics.repartition.trashCount,
          sizeFiles: this.info.metrics.repartition.trashSize,
          value: trashPc
        });
      }

      if (orphanPc > 0) {
        data.push({
          category: this.$t("AdminCenterVaultManager.Orphans"),
          color: "#ffc107",
          nbFiles: this.info.metrics.repartition.orphanCount,
          sizeFiles: this.info.metrics.repartition.orphanSize,
          value: orphanPc
        });
      }

      if (freeSize > 0) {
        data.push({
          category: this.$t("AdminCenterVaultManager.Free"),
          color: "#28a644",
          nbFiles: 0,
          sizeFiles: totalSize - this.info.metrics.usedSize,
          value: freeSize
        });
      }
    } else {
      data.push({
        category: this.$t("AdminCenterVaultManager.No data"),
        color: "#28a644",
        nbFiles: 0,
        sizeFiles: 0,
        value: 0
      });
    }
    return [
      {
        data,
        type: "pie"
      }
    ];
  }

  // noinspection JSMethodCanBeStatic
  public closeWindow(e) {
    let sender;
    if (e.sender) {
      sender = e.sender.element;
    } else {
      sender = $(e.target);
    }

    sender
      .closest("[data-role=window]")
      .data("kendoWindow")
      .close();
  }

  public requestMoveIt(e) {
    this.closeWindow(e);
    this.$http
      .put("/api/v2/admin/vaults/" + this.info.fsid + "/path/", $(this.$refs.newPath).val(), {
        headers: { "Content-Type": "text/plain" }
      })
      .then(response => {
        const data = response.data.data;

        if (data && response.data.messages) {
          this.requestMessage = response.data.messages[0].contentText;
        }
        this.$emit("vault-updated", data);
      });
  }

  public requestResizeIt(e) {
    this.closeWindow(e);
    const newSize: string = $(this.$refs.newSize).val() as string;
    const kSizeUnit: any = this.$refs.kNewSizeUnit as any;
    this.$http
      .put(
        "/api/v2/admin/vaults/" + this.info.fsid + "/size/",
        Math.floor(parseFloat(newSize) * parseFloat(kSizeUnit.kendoWidget().value())),
        {
          headers: { "Content-Type": "text/plain" }
        }
      )
      .then(response => {
        const data = response.data.data;

        if (data && response.data.messages) {
          this.requestMessage = response.data.messages[0].contentText;
        }
        this.$emit("vault-updated", data);
      });
  }

  public onMovePath() {
    $(this.$refs.movePathForm)
      .kendoWindow({
        actions: ["Close"],
        modal: true,
        title: `${this.$t("AdminCenterVaultManager.Move path")}`,
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
        title: `${this.$t("AdminCenterVaultManager.Resize logical volume")}`,
        visible: false
      })
      .data("kendoWindow")
      .center()
      .open();
  }

  public logicalTemplate() {
    return `<b>${this.convertBytes(this.info.metrics.usedSize)}</b> ${this.$t(
      "AdminCenterVaultManager.used out of"
    )} <b>${this.convertBytes(this.info.metrics.totalSize)}</b> (<b>${Math.floor(
      (this.info.metrics.usedSize / this.info.metrics.totalSize) * 100
    )}%</b>)`;
  }

  public diskTemplate() {
    return `<b>${this.convertBytes(this.info.disk.usedSize)}</b>  ${this.$t(
      "AdminCenterVaultManager.used out of"
    )} <b>${this.convertBytes(this.info.disk.totalSize)}</b> (<b>${Math.floor(
      (this.info.disk.usedSize / this.info.disk.totalSize) * 100
    )}%</b>)`;
  }

  public redrawGauge() {
    let gauge = this.$refs.logicalGauge;
    if (gauge) {
      // @ts-ignore
      let kGauge = gauge.kendoWidget();
      if (kGauge) {
        kGauge.setOptions({ transitions: false });
        kGauge.redraw();
      }
      gauge = this.$refs.diskGauge;
      // @ts-ignore
      kGauge = gauge.kendoWidget();
      if (kGauge) {
        kGauge.setOptions({ transitions: false });
        kGauge.redraw();
      }

      gauge = this.$refs.chart;
      // @ts-ignore
      kGauge = gauge.kendoWidget();
      if (kGauge) {
        kGauge.setOptions({ transitions: false });
        kGauge.redraw();
      }
    }
  }

  public mounted() {
    window.addEventListener("resize", this.redrawGauge);
  }
  public beforeDestroy() {
    window.removeEventListener("resize", this.redrawGauge);
  }
}
