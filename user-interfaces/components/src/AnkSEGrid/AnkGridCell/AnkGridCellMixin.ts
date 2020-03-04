import { Component, Prop, Vue } from "vue-property-decorator";
import AnkSmartElementGrid, { SmartGridCellValue, SmartGridColumn, SmartGridRowData } from "../AnkSEGrid.component";

@Component({})
export default class GridFilterCell extends Vue {
  @Prop({
    type: String
  })
  public field!: string;
  @Prop({
    type: Object
  })
  public dataItem!: SmartGridRowData;
  @Prop({
    type: Object
  })
  public columnConfig!: SmartGridColumn;
  @Prop({
    type: String
  })
  public format!: string;
  @Prop({
    type: String
  })
  public className!: string;
  @Prop({
    type: String
  })
  public rowType!: string;
  @Prop({
    type: Number
  })
  public columnIndex!: number;
  @Prop({
    type: Number
  })
  public columnsCount!: number;
  @Prop({
    type: Number
  })
  public level!: number;
  @Prop({
    type: Boolean
  })
  public property!: boolean;
  @Prop({
    type: String
  })
  public editor!: string;
  @Prop({
    type: Boolean
  })
  public expanded!: boolean;
  @Prop({
    default: () => null
  })
  public fieldValue!: SmartGridCellValue;
  @Prop({
    type: Object
  })
  public gridComponent!: AnkSmartElementGrid;
  @Prop({
    type: String,
    default: "td"
  })
  public tag!: string;
}
