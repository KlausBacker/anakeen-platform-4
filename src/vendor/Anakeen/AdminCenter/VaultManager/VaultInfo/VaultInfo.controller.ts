// @ts-ignore
import { Chart, ChartInstaller } from "@progress/kendo-charts-vue-wrapper";
import { ArcGauge, GaugesInstaller } from "@progress/kendo-gauges-vue-wrapper";
import "@progress/kendo-ui/js/kendo.dataviz";
import { Component, Prop, Vue } from "vue-property-decorator";

Vue.use(ChartInstaller);
Vue.use(GaugesInstaller);

@Component({
  name: "ank-vault-info"
})
export default class VaultInfoController extends Vue {
  @Prop({ type: Object as () => {} }) public info;

  // public labelTemplate: string = "#= category # - #= dataItem ##= kendo.format('{0:P}', percentage)#";

  public convertBytes(x) {
    const units = ["bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
    let l = 0;
    let n = parseInt(x, 10) || 0;
    while (n >= 1024 && ++l) {
      n = n / 1024;
    }
    return n.toFixed(n < 10 && l > 0 ? 1 : 0) + " " + units[l];
  }

  public labelTemplate(data) {
    return `${data.category} - ${
      data.dataItem.nbFiles
    } files \n ${this.convertBytes(data.dataItem.sizeFiles)} (${
      data.dataItem.value
    }%)`;
  }

  public logicalTemplate(data) {
    return `<b>${this.convertBytes(
      this.info.metrics.usedSize
    )}</b> used on <b>${this.convertBytes(
      this.info.metrics.totalSize
    )}</b> of logical capacity (<b>${Math.floor(
      (this.info.metrics.usedSize / this.info.metrics.totalSize) * 100
    )}%</b>)`;
  }

  public diskTemplate() {
    return `<b>${this.convertBytes(
      this.info.disk.usedSize
    )}</b> used on <b>${this.convertBytes(
      this.info.disk.totalSize
    )}</b> of disk capacity (<b>${Math.floor(
      (this.info.disk.usedSize / this.info.disk.totalSize) * 100
    )}%</b>)`;
  }

  public mounted() {
    $(".modify-btn", this.$el).kendoButton();
    $(".move-btn", this.$el).kendoButton();
  }
}
