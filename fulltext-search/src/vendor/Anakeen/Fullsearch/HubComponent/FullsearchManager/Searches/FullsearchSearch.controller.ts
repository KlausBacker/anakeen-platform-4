import { Component, Prop, Vue } from "vue-property-decorator";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import ResultList from "../Results/FullsearchResultList.vue";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";

Vue.use(ButtonsInstaller);
@Component({
  name: "ank-fullsearch-search",
  components: {
    "ank-split-panes": AnkPaneSplitter,
    "search-results": ResultList,
    "ank-smart-element": (): Promise<unknown> => AnkSmartElement
  }
})
export default class FullsearchSearchController extends Vue {
  @Prop({ default: null, type: String })
  public domain!: string;

  public pattern = "";
  public searchPattern = "";
  public selectedElement = "";
  public elementList = null;
  public configs = false;
  public selectElement(elementId): void {
    this.selectedElement = elementId;
  }
  public mounted() {
    kendo.ui.progress($(".fullsearch-search", this.$el), true);
    this.$http
      .get("/api/admin/fullsearch/domains/")
      .then(response => {
        kendo.ui.progress($(".fullsearch-search", this.$el), false);
        const configsTab = response.data.data.config;
        this.configs = configsTab.length > 0;
      })
      .catch(reponse => {
        kendo.ui.progress($(".fullsearch-search", this.$el), false);
        console.error(reponse);
      });
  }
  public search(): void {
    kendo.ui.progress($(".fullsearch-results", this.$el), true);
    this.$http
      .get(`/api/v2/fullsearch/domains/${this.domain}/smart-elements/`, {
        params: {
          q: this.pattern
        }
      })
      .then(response => {
        kendo.ui.progress($(".fullsearch-results", this.$el), false);
        this.searchPattern = this.pattern;
        this.elementList = response.data.data.documents;
      })
      .catch(response => {
        kendo.ui.progress($(".fullsearch-results", this.$el), false);
        console.error(response);
      });
  }
}
