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

  protected get isMultiple() {
    return this.columnConfig.multiple || Array.isArray(this.cellValue);
  }

  protected get cellValue() {
    if (this.fieldValue) {
      return this.fieldValue;
    } else if (this.dataItem) {
      if (this.columnConfig.property) {
        return this.dataItem.properties[this.field];
      } else if (this.columnConfig.abstract) {
        return this.dataItem.abstract[this.field];
      } else {
        return this.dataItem.attributes[this.field];
      }
    }
    return null;
  }

  protected get isEmpty() {
    if (this.isMultiple) {
      return !this.cellValue.length;
    } else if (this.columnConfig.property) {
      return !this.cellValue;
    } else {
      return !(this.cellValue.diplayValue || this.cellValue.value);
    }
  }

  protected get isInexistent() {
    return !this.cellValue;
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
