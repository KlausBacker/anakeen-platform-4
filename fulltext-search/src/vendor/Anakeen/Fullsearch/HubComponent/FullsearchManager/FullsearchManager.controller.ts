import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import SearchConfigs from "./FullsearchConfig.vue";
import SearchSearch from "./FullsearchSearch.vue";
// eslint-disable-next-line no-unused-vars
import { IDomainConfig } from "./IDomainConfigType";

import "@progress/kendo-ui/js/kendo.splitter";

import { Component, Vue } from "vue-property-decorator";

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    "ank-split-panes": AnkPaneSplitter,
    "search-configs": SearchConfigs,
    "search-search": SearchSearch
  }
})
export default class FullsearchManagerController extends Vue {
  public domainConfig = "";

  protected onSelectedDomain(selectedDomain): void {
    this.domainConfig = selectedDomain;
  }
}
