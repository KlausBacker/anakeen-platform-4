import { Component, Mixins, Prop, Watch } from "vue-property-decorator";
import AnkGrid from "../AnkSEGrid/AnkSEGrid.vue";
import EventUtilsMixin from "../../mixins/AnkVueComponentMixin/EventUtilsMixin";
import I18nMixin from "../../mixins/AnkVueComponentMixin/I18nMixin";
import ReadyMixin from "../../mixins/AnkVueComponentMixin/ReadyMixin";
import AnkSmartElementGrid, {
  SmartGridColumn,
  SmartGridFilter,
  SmartGridPageable
} from "../AnkSEGrid/AnkSEGrid.component";
import ListEvent from "./AnkListEvent/AnkListEvent";
import AnkGridPager from "../AnkSEGrid/AnkGridPager/AnkGridPager.vue";

const CONTROLLER_URL = "/api/v2/grid/controllers/{controller}/{op}/{collection}";
@Component({
  name: "ank-se-list",
  components: {
    AnkGrid,
    AnkGridPager
  }
})
export default class SeListComponent extends Mixins(EventUtilsMixin, ReadyMixin, I18nMixin) {
  @Prop({ type: String, default: "" }) public smartCollection;
  @Prop({ type: Boolean, default: true }) public selectable;
  @Prop({ type: Array, default: () => [] }) public attachedData;
  @Prop({ type: String, default: "" }) public label;
  @Prop({ type: Boolean, default: true }) public autoFit: true;
  @Prop({ type: String, default: CONTROLLER_URL }) public contentUrl: string;
  @Prop({ type: String, default: "DEFAULT_GRID_CONTROLLER" }) public controller: string;
  @Prop({ type: Number, default: 500 }) public sBreakpoint: number;
  @Prop({ type: Number, default: 210 }) public xsBreakpoint: number;
  @Prop({ type: Number, default: 0 }) public value!: number;
  @Prop({ type: Number, default: 1 }) public page!: number;
  @Prop({ type: String, default: "" }) public filterValue!: string;
  @Prop({ type: Object, default: () => ({ field: "title", dir: "asc" }) }) public sort!: kendo.data.DataSourceSortItem;
  @Prop({ type: [Object, Boolean], default: true}) public pageable!: SmartGridPageable | boolean;
  @Watch("smartCollection")
  protected onSmartCollectionChange(newValue): void {
    this.collectionId = newValue;
  }

  @Watch("value")
  protected onValuePropChange(newValue): void {
    if (this.selectable && this.$refs.internalWidget) {
      this.selectSmartElement(newValue);
    }
  }

  @Watch("filterValue")
  protected onFilterValuePropChange(newValue): void {
    if (this.$refs.internalWidget) {
      this.filterList(newValue);
    }
  }

  @Watch("filterInput")
  protected onFilterInputDataChange(newValue): void {
    const listEvent = new ListEvent(
      {
        filterInput: newValue
      },
      null,
      false
    );
    this.$emit("filterInput", listEvent);
  }

  public collectionId: string | number = this.smartCollection;
  public selectedItem: string | number = "";
  public filterInput = this.filterValue;
  public small = false;
  public xSmall = false;
  public currentFilter: SmartGridFilter = {};
  public collectionInfoReady = false;

  public $refs: {
    internalWidget: AnkSmartElementGrid;
  };

  protected get columns(): SmartGridColumn[] {
    return [
      {
        field: "title",
        property: true
      },
      {
        field: "state",
        property: true,
        hidden: true
      },
      ...this.attachedData.map(d => {
        return {
          ...d,
          hidden: true
        };
      })
    ];
  }

  public get translations(): { [key: string]: string } {
    const searchTranslated = this.$t("selist.Search in : {collection}", {
      collection: this.listLabel.toUpperCase()
    });
    const noDataTranslated = this.$t("selist.No {collection} to display", { collection: this.listLabel });
    return {
      itemsPerPageLabel: this.$t("selist.Items per page") as string,
      noDataPagerLabel: noDataTranslated as string,
      searchPlaceholder: searchTranslated as string
    };
  }

  public get listLabel(): string {
    if (this.label) {
      return this.label;
    } else if (this.collectionInfoReady) {
      return this.$refs.internalWidget.collectionProperties.title;
    } else {
      return "";
    }
  }

  public created(): void {
    $(window).on(`resize.smartList${this._uid}`, this.onResize);
  }

  public mounted(): void {
    if (this.selectable && this.$refs.internalWidget && this.value) {
      this.$refs.internalWidget.$once("dataBound", () => {
        this.selectSmartElement(this.value);
      });
    }
    if (this.$refs.internalWidget && this.filterValue) {
      this.$refs.internalWidget.$once("dataBound", () => {
        this.filterList(this.filterValue);
      });
    }
    this.$watch(
      () => this.$refs.internalWidget.collectionProperties,
      (newValue, oldValue) => {
        this.collectionInfoReady = !!newValue.title;
      }
    );
    this.onResize();
  }

  public beforeDestroy(): void {
    $(window).off(`.smartList${this._uid}`);
  }

  public setCollection(collection) {
    this.collectionId = collection;
  }

  public filterList(filterValue): void {
    if (!filterValue) {
      this.clearListFilter();
    } else {
      this.filterInput = filterValue;
      const filterObject = {
        field: "title",
        operator: "contains",
        value: filterValue
      };
      const listEvent = new ListEvent(
        {
          filterInput: filterValue,
          filter: filterObject
        },
        null,
        false
      );
      this.currentFilter = filterObject;
      this.$emit("filterChange", listEvent);
    }
  }

  public clearListFilter(): void {
    this.currentFilter = {};
    this.filterInput = "";
    const listEvent = new ListEvent(
      {
        filterInput: ""
      },
      null,
      false
    );
    this.$emit("filterChange", listEvent);
    this.$emit("filterClear", listEvent);
  }

  public selectSmartElement(smartElementId: number): void {
    if (this.selectable) {
      this.$refs.internalWidget.selectedRows = [smartElementId.toString()];
    }
  }

  public async refreshList(): Promise<void> {
    return this.$refs.internalWidget.refreshGrid(true);
  }

  public scrollToActiveItem(): void {
    const activeItem = this.$el.querySelector(".k-state-selected");
    if (activeItem) {
      activeItem.scrollIntoView();
    }
  }

  protected onResize(): void {
    if (this.$el.clientWidth) {
      this.small = this.$el.clientWidth < this.sBreakpoint;
      this.xSmall = this.$el.clientWidth < this.xsBreakpoint;
    }
  }

  protected onSelectionChange(event): void {
    const data = event.data;
    if (data && data.selectedRows && data.selectedRows.length) {
      const id = parseInt(data.selectedRows[0]);
      if (isNaN(id)) {
        this.$emit("input", data.selectedRows[0]);
      } else {
        this.$emit("input", id);
      }
    }
  }

  protected onItemClick(event): void {
    const data = event.data;
    const listEvent = new ListEvent(data.dataItem, null, false);
    if (data && data.dataItem) {
      this.$emit("itemClicked", listEvent);
    }
    if (this.selectable) {
      this.$emit("itemSelected", listEvent);
    }
  }

  protected onDataBound(event): void {
    const listEvent = new ListEvent(event.data, null, false);
    this.$emit("dataBound", listEvent);
  }

  protected onPageChange(event): void {
    const listEvent = new ListEvent(event.data, null, false);
    this.$emit("pageChange", listEvent);
  }

  protected onBeforeContent(event): void {
    const listEvent = new ListEvent(event.data, null, true);
    this.$emit("beforeContent", listEvent);
    event.data = listEvent.data;
    if (listEvent.isDefaultPrevented()) {
      event.preventDefault();
    }
  }
}
