import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import { process } from "@progress/kendo-data-query";
import { kendoGrid } from "@progress/kendo-vue-grid";
import { IDomainConfig } from "./IDomainConfigType";
// declare var $;
// declare var kendo;

// noinspection JSUnusedGlobalSymbols
@Component({
  name: "ank-fullsearch-list",

  components: {
    "kendo-grid": kendoGrid
  }
})
export default class FullsearchConfigController extends Vue {
  public domains: IDomainConfig[] = [];
  public mounted() {
    this.fetchConfigs();
  }
  protected fetchConfigs() {
    this.$http.get("/api/admin/fullsearch/domains/").then(response => {
      this.domains = response.data.data.config;
    });
  }
}
