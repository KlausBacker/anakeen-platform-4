import { Component, Prop, Vue } from "vue-property-decorator";
import { SmartGridColumn } from "../AnkSEGrid.component";
import GridController from '../AnkSEGrid.component';

@Component({})
export default class GridFilterCell extends Vue {
  @Prop({
    type: String
  })
  public field!: string;
  @Prop({
    type: Object
  })
  public dataItem!: any;
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
    type: Object
  })
  public fieldValue!: any;
  @Prop({
    type: Object
  })
  public gridComponent!: GridController;
}
