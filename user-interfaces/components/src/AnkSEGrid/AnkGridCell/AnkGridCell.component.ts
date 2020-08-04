import { Component, Mixins } from "vue-property-decorator";
import AnkGridCellMixin from "./AnkGridCellMixin";
import SimpleText from "./AnkGridCellTypes/AnkGridCellText.vue";
import HtmlText from "./AnkGridCellTypes/AnkGridCellHtmlText.vue";
import IconText from "./AnkGridCellTypes/AnkGridCellIconText.vue";
import Color from "./AnkGridCellTypes/AnkGridCellColor.vue";
import { SmartGridCellFieldValue, SmartGridCellValue } from "../AnkSEGrid.component";

@Component({
  name: "ank-se-grid-cell",
  components: {
    SimpleText,
    HtmlText,
    IconText,
    Color
  }
})
export default class GridFilterCell extends Mixins(AnkGridCellMixin) {
  public selectedOperator = null;
  public filterValue = "";

  protected dateFormat(value): string {
    return kendo.toString(new Date(value), "G");
  }
  protected getSublevel(field): string[] {
    if (Array.isArray(field)) {
      return field;
    } else {
      return [field];
    }
  }

  protected get isMultiple(): boolean {
    return this.columnConfig.multiple || Array.isArray(this.cellValue);
  }

  protected get cellValue(): SmartGridCellValue {
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

  protected get isEmpty(): boolean {
    if (this.isMultiple) {
      const cellValue = this.cellValue as SmartGridCellFieldValue[];
      return !cellValue.length;
    } else if (this.columnConfig.property) {
      return this.cellValue === null || this.cellValue === undefined;
    } else {
      const cellValue = this.cellValue as SmartGridCellFieldValue;
      return cellValue.value !== 0 && !(cellValue.displayValue || cellValue.value);
    }
  }

  protected get isInexistent(): boolean {
    return this.cellValue === null;
  }

  public get componentName(): "IconText" | "Color" | "HtmlText" | "SimpleText" {
    switch (this.columnConfig.smartType) {
      case "docid":
      case "account":
      case "file":
      case "image":
        return "IconText";
      case "color":
        return "Color";
      case "htmltext":
        return "HtmlText";
      default:
        return "SimpleText";
    }
  }

  protected onClickItem(): void {
    this.$emit("itemClick");
  }
}
