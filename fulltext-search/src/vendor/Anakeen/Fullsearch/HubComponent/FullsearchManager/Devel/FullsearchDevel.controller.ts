import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import SearchConfigs from "../ExpandList/FullsearchExpandList.vue";
import SearchSearch from "../Searches/FullsearchSearch.vue";

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
export default class FullsearchDevelController extends Vue {
  public domainConfig = "";

  protected onSelectedDomain(selectedDomain): void {
    this.domainConfig = selectedDomain;
  }
}
