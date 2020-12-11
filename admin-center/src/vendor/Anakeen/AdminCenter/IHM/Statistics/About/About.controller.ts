import { Component, Mixins } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
//import kendo from "@progress/kendo-ui/js/kendo.core";

@Component
export default class AboutController extends Mixins(AnkI18NMixin) {
  public currentVersion = "";
  public commitDate = "";
  public localeCommitDate = "";
  public getVersion(): void {
    this.$http.get(`/api/v2/about/version`).then(response => {
      this.currentVersion = response.data.data.version;
      this.commitDate = response.data.data.commitDate;

      const cDate = kendo.parseDate(this.commitDate);
      this.localeCommitDate = kendo.toString(cDate, "D") + " " + kendo.toString(cDate, "T");
    });
  }
  public mounted(): void {
    this.getVersion();
  }
}
