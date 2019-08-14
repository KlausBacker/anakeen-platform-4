import Grid from "./Grid.vue";
import GridPager from "./Components/GridPager/GridPager";
import GridExpandButton from "./Components/GridExpandButton/GridExpandButton";
import GridExportButton from "./Components/GridExportButton/GridExportButton";

export default function install(Vue) {
  Vue.component("ank-se-grid", Grid);
  Vue.component("ank-se-grid-pager", GridPager);
  Vue.component("ank-se-grid-expand-button", GridExpandButton);
  Vue.component("ank-se-grid-export-button", GridExportButton);
}
