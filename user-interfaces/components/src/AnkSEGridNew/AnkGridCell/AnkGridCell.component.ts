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

  protected get isEmpty() {
    if (this.columnConfig.property) {
      return !(this.dataItem && this.dataItem.properties && this.dataItem.properties[this.field]);
    } else if (this.columnConfig.abstract) {
      return !(
        this.dataItem &&
        this.dataItem.abstract &&
        this.dataItem.abstract[this.field] &&
        (this.dataItem.abstract[this.field].displayValue || this.dataItem.abstract[this.field].value)
      );
    } else {
      return !(
        this.dataItem &&
        this.dataItem.attributes &&
        this.dataItem.attributes[this.field] &&
        (this.dataItem.attributes[this.field].displayValue || this.dataItem.attributes[this.field].value)
      );
    }
  }

  protected get isInexistent() {
    if (this.columnConfig.property) {
      return !(this.dataItem && this.dataItem.properties && this.dataItem.properties[this.field] !== undefined);
    } else if (this.columnConfig.abstract) {
      return !(
        this.dataItem &&
        this.dataItem.abstract &&
        this.dataItem.abstract[this.field] !== undefined
      );
    } else {
      return !(
        this.dataItem &&
        this.dataItem.attributes &&
        this.dataItem.attributes[this.field] !== undefined
      );
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
