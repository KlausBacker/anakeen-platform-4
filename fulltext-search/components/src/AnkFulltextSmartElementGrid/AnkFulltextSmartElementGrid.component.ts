import { Component, Prop, Watch } from "vue-property-decorator";
import AnkSmartElementGridVueComponent from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import AnkSmartElementGrid, {
  SmartGridInfo
} from "@anakeen/user-interfaces/components/src/AnkSEGrid/AnkSEGrid.component";
export interface SmartGridFulltextSearch {
  searchDomain: string;
  searchPattern: string;
}

export interface SmartGridFulltextInfo extends SmartGridInfo {
  fulltextSearch: SmartGridFulltextSearch;
}

function computePage(currentPage, pageable): number {
  return pageable.pageSize && pageable.pageSize !== "ALL"
    ? (currentPage.skip + currentPage.take) / currentPage.take
    : 1;
}

@Component({
  name: "ank-fulltext-smart-element-grid",
  extends: AnkSmartElementGridVueComponent
})
export default class AnkSmartElementFulltextGrid extends AnkSmartElementGrid {
  @Prop({
    default: () => null,
    type: Object
  })
  public fulltextSearch!: SmartGridFulltextSearch;

  @Prop({
    default: "DEFAULT_GRID_FULLTEXT_CONTROLLER",
    type: String
  })
  public controller: string;

  @Watch("fulltextSearch", { deep: true })
  public async watchFulltext(): Promise<void> {
    return await this.refreshGrid();
  }

  public get gridInfo(): SmartGridFulltextInfo {
    return {
      columns: this.columns,
      actions: this.actions,
      controller: this.controller,
      collection: this.collection,
      pageable: this.pager,
      page: computePage(this.currentPage, this.pager),
      sortable: this.sorter,
      sort: this.currentSort,
      filterable: this.filterable,
      filter: this.currentFilter,
      transaction: this.transaction,
      selectedRows: this.selectedRows,
      onlySelection: this.onlySelection,
      fulltextSearch: this.fulltextSearch,
      customData: this.customData,
      contentUrl: this._getOperationUrl("content"),
      configUrl: this._getOperationUrl("config"),
      exportUrl: this._getOperationUrl("export")
    };
  }
}
