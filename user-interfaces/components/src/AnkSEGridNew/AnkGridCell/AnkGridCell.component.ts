import { Component, Mixins, Prop, Vue } from "vue-property-decorator";
import { SmartGridColumn } from "../AnkSEGrid.component";
import AnkGridCellMixin from "./AnkGridCellMixin";

@Component({
  name: "ank-se-grid-cell",
  components: {
    simpleText: () => import("./AnkGridCellTypes/AnkGridCellText.vue"),
    htmlText: () => import("./AnkGridCellTypes/AnkGridCellHtmlText.vue"),
    iconText: () => import("./AnkGridCellTypes/AnkGridCellIconText.vue"),
    color: () => import("./AnkGridCellTypes/AnkGridCellColor.vue")
  }
})
export default class GridFilterCell extends Mixins(AnkGridCellMixin) {
  public selectedOperator = null;
  public filterValue: string = "";

  protected getSublevel(field) {
    if (Array.isArray(field)) {
      return field;
    } else {
      return [field];
    }
  }

  public get componentName() {
    switch (this.columnConfig.smartType) {
      case "docid":
      case "account":
      case "file":
      case "image":
        return "iconText";
      case "color":
        return "color";
      case "htmltext":
        return "htmlText";
      default:
        return "simpleText";
    }
  }
}
