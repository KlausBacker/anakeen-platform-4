import { Component, Mixins } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

@Component
export default class AboutController extends Mixins(AnkI18NMixin) {
  public currentVersion = "";
  public getVersion(): void {
    this.$http.get(`/api/v2/about/version`).then(response => {
      this.currentVersion = response.data.data.version;
    });
  }
  public mounted(): void {
    this.getVersion();
  }
}
