import { Component, Mixins, Prop } from "vue-property-decorator";

import splitpanes from "splitpanes";
import "splitpanes/dist/splitpanes.css";

// noinspection JSUnusedGlobalSymbols
@Component({
  name: "ank-pane-splitter"
})
export default class SplitterComponent extends Mixins(splitpanes) {
  @Prop({ default: false, type: Boolean }) public collapseButtons!: boolean;

  panes: { width: number; savedWidth: number; index: number; max: number; min: number }[] = [];
  private previousWidths: number[] = [];
  public updated(): void {
    this.keepClassTheme();
  }
  public beforeUpdate(): void {
    this.panes.forEach(pane => {
      pane.savedWidth = pane.width;
    });
  }
  public mounted(): void {
    if (this.collapseButtons) {
      this.addCollapseExpand();
    }
    this.keepClassTheme();

    this.$on("resize", () => {
      window.dispatchEvent(new Event("resize"));
      this.previousWidths = [];
    });
  }

  protected addCollapseExpand(): void {
    const childs = this.$el.children; // querySelectorAll("> .splitpanes__splitter");
    let index = 0;
    [].forEach.call(childs, (splitDom: HTMLElement) => {
      if (splitDom.classList.contains("splitpanes__splitter")) {
        const collapseLeft = document.createElement("div");
        const expandeRight = document.createElement("div");
        collapseLeft.className = "collapse-left";
        expandeRight.className = "expand-right";
        splitDom.appendChild(collapseLeft);
        splitDom.appendChild(expandeRight);
        collapseLeft.dataset.index = index.toString(10);
        collapseLeft.addEventListener("click", () => {
          const sIndex = collapseLeft.dataset.index as string;
          const rIndex = parseInt(sIndex, 10);
          if (this.panes[rIndex].width > 0) {
            if (this.previousWidths[rIndex + 1]) {
              this.panes[rIndex + 1].width = this.previousWidths[rIndex + 1];
              this.panes[rIndex].width -= this.previousWidths[rIndex + 1];
              this.previousWidths[rIndex + 1] = 0;
            } else {
              const rightWidth = this.panes[rIndex].width;
              this.previousWidths[rIndex] = this.panes[rIndex].width;
              this.panes[rIndex].width = 0;
              this.panes[rIndex + 1].width += rightWidth;
            }

            window.dispatchEvent(new Event("resize"));
          }
        });
        expandeRight.dataset.index = index.toString(10);
        expandeRight.addEventListener("click", () => {
          const sIndex = expandeRight.dataset.index as string;
          const rIndex = parseInt(sIndex, 10);
          if (this.panes[rIndex + 1]) {
            if (this.panes[rIndex + 1].width > 0) {
              if (this.previousWidths[rIndex]) {
                this.panes[rIndex].width += this.previousWidths[rIndex];
                this.panes[rIndex + 1].width -= this.previousWidths[rIndex];
                this.previousWidths[rIndex] = 0;
              } else {
                const rightWidth = this.panes[rIndex + 1].width;

                // this.previousWidths[rIndex] = this.panes[rIndex].width;
                this.previousWidths[rIndex + 1] = this.panes[rIndex + 1].width;
                this.panes[rIndex].width += rightWidth;
                this.panes[rIndex + 1].width = 0;
              }

              window.dispatchEvent(new Event("resize"));
            }
          }
        });
        index++;
      }
    });
  }

  protected keepClassTheme(): void {
    this.$el.classList.add("default-theme");
    this.$el.classList.add("splitter-anakeen-theme");
  }
}
