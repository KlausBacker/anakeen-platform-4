import { Component, Prop, Watch, Vue } from "vue-property-decorator";
import AnkProgress from "./AnkProgress/AnkProgress.vue";
import { Grid } from "@progress/kendo-vue-grid";
import { VNode } from "vue/types/umd";

const CONTROLLER_URL = "/api/v2/grid/controllers/{controller}/{op}/{collection}";

interface SmartGridColumn {
  field: string;
  smartType: string;
  title: string;
  abstract: boolean;
  property: boolean;
  encoded: boolean;
  hidden: boolean;
  sortable: boolean;
  filterable: boolean | object;
}

@Component({
  components: {
    "kendo-grid-vue": Grid,
    "ank-progress": AnkProgress
  },
  name: "ank-se-grid-vue"
})
export default class GridController extends Vue {
  @Prop({
    default: "",
    type: String
  })
  public collection: string;
  @Prop({
    default: () => [],
    type: Array
  })
  public columns: SmartGridColumn[];
  @Prop({
    default: () => [],
    type: String
  })
  public controller: string;
  @Prop({
    default: "",
    type: String
  })
  public persistStateKey: string;
  @Prop({
    default: true,
    type: Boolean
  })
  public contextTitles: boolean;

  @Prop({
    default: true,
    type: Boolean
  })
  public collapseRowButton: boolean;

  @Prop({
    default: false,
    type: Boolean
  })
  public exportButton: boolean;

  @Prop({
    default: "-",
    type: String
  })
  public contextTitlesSeparator: string;

  @Prop({
    default: "",
    type: String
  })
  public emptyCell;
  @Prop({
    default: () => ({
      mode: "multiple",
      showIndexes: true
    }),
    type: [String, Boolean, Object]
  })
  public sortable: string | boolean | object;

  @Prop({
    default: "menu",
    type: String
  })
  public filterable: boolean;
  @Prop({
    default: false,
    type: Boolean
  })
  public refresh: boolean;
  @Prop({
    default: false,
    type: Boolean
  })
  public reorderable: boolean;

  @Prop({
    default: true,
    type: Boolean
  })
  public pageable: boolean;
  @Prop({
    default: () => [10, 20, 50],
    type: [Boolean, Array]
  })
  public pageSizes: boolean | Array<number>;

  @Prop({
    default: true,
    type: Boolean
  })
  public resizable: boolean;
  @Prop({
    default: false,
    type: [Boolean, String]
  })
  public selectable: boolean | string;
  @Prop({
    default: false,
    type: Boolean
  })
  public checkable: boolean;

  @Prop({
    default: true,
    type: Boolean
  })
  public persistSelection: boolean;

  public columnsList: any = [];
  public dataItems: any = [];
  public isLoading: boolean = false;

  async created() {
    this.isLoading = true;
    try {
      await this._loadGridConfig();
      await this._loadGridContent();
      this.isLoading = false;
    } catch (error) {
      console.error(error);
    }
  }

  mounted() {
    let saveColumnsOptions = null;
    if (this.persistStateKey) {
      if (window && window.localStorage) {
        saveColumnsOptions = localStorage.getItem(this.persistStateKey);
        if (saveColumnsOptions) {
          saveColumnsOptions = JSON.parse(saveColumnsOptions);
        }
      } else {
        console.error("Persistent grid state is disabled, local storage is not supported by the current environment");
      }
    }
  }

  protected async _loadGridConfig() {
    const url = this._getOperationUrl("config");
    const response = await this.$http.get(url, {
      params: {
        columns: this.columns
      }
    });
    this.columnsList = response.data.data.columns;
  }

  protected async _loadGridContent() {
    const url = this._getOperationUrl("content");
    const response = await this.$http.get(url, {
      params: {
        columns: this.columns
      }
    });
    this.dataItems = response.data.data.content;
  }

  protected cellRenderFunction(createElement, tdElement: VNode, props, listeners) {
    const columnConfig = this.columnsList[props.columnIndex];
    if (columnConfig.property) {
      return createElement("td", props.dataItem.properties[columnConfig.field]);
    } else if (columnConfig.abstract) {
      return createElement("td", props.dataItem.abstract[columnConfig.field].displayValue);
    } else {
      return createElement("td", Object.assign({}, props.dataItem.attributes[columnConfig.field]).displayValue);
    }
    return tdElement;
  }

  protected _getOperationUrl(operation) {
    return CONTROLLER_URL.replace(/\{(\w+)\}/g, (match, substr, ...args) => {
      switch (substr) {
        case "controller":
          return this.controller;
        case "op":
          return operation;
        case "collection":
          return this.collection;
        default:
          return substr;
      }
    });
  }
}
