import { Component, Vue } from "vue-property-decorator";

@Component({
  name: "ank-se-grid-cell",
  props: {
    field: String,
    dataItem: Object,
    format: String,
    className: String,
    columnIndex: Number,
    columnsCount: Number,
    rowType: String,
    level: Number,
    expanded: Boolean,
    editor: String,
    property: Boolean,
    columnConfig: Object
  }
})
export default class GridFilterCell extends Vue {
  public selectedOperator = null;
  public filterValue: string = "";

  public created() {
    console.log(this);
  }
}
