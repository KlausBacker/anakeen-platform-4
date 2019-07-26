import { Component, Mixins } from "vue-property-decorator";

import splitpanes from "splitpanes";
import "splitpanes/dist/splitpanes.css";

// noinspection JSUnusedGlobalSymbols
@Component({
  name: "ank-pane-splitter"
})
export default class SplitterComponent extends Mixins(splitpanes) {
  private panes: any;
  public updated() {
    this.keepClassTheme();
  }
  public beforeUpdate() {
    this.panes.forEach(pane => {
      pane.savedWidth = pane.width;
    });
  }
  public mounted() {
    this.keepClassTheme();
    this.$on("resized", () => {
      window.dispatchEvent(new Event("resize"));
    });
  }
  protected keepClassTheme() {
    this.$el.classList.add("default-theme");
    this.$el.classList.add("splitter-anakeen-theme");
  }
}
