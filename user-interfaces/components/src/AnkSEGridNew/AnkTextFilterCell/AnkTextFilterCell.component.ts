import { Component, Mixins } from "vue-property-decorator";
import { DropDownList } from "@progress/kendo-vue-dropdowns";
import { Input } from "@progress/kendo-vue-inputs";
import FilterCellMixin from "../AnkFilterCellMixin";

@Component({
  name: "ank-se-grid-filter-cell",
  components: {
    "dropdown-list": DropDownList,
    "text-input": Input
  }
})
export default class GridFilterCell extends Mixins(FilterCellMixin) {
  public selectedOperator = null;
  public filterValue: string = "";

  public created() {
    this.selectedOperator = this.filterOperators && this.filterOperators.length ? this.filterOperators[0] : null;
  }

  public get filterOperators() {
    const filterType = "string";
    if (this.columnConfig) {
      if (this.columnConfig.filterable.operators && this.columnConfig.filterable.operators[filterType]) {
        return Object.keys(this.columnConfig.filterable.operators[filterType]).map(operatorKey => {
          return {
            key: operatorKey,
            value: this.columnConfig.filterable.operators[filterType][operatorKey]
          };
        });
      }
    }
    return [];
  }

  public filter(value) {
    this.$emit("change", {
      operator: this.selectedOperator ? this.selectedOperator.key : "",
      field: this.field,
      value: value
    });
  }

  public reset() {
    this.filterValue = "";
    this.$emit("change", {
      operator: "",
      field: this.field,
      value: ""
    });
  }
}
