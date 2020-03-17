import { Component, Prop, Vue } from "vue-property-decorator";
import AnkGridFilter from "./AnkGridFilter/AnkGridFilter.vue";
import AnkSmartElementGrid from "../AnkSEGrid.component";
import { Popup } from "@progress/kendo-vue-popup";
import $ from "jquery";

@Component({
  name: "ank-se-grid-header-cell",
  components: {
    AnkGridFilter,
    Popup
  }
})
export default class GridHeaderCell extends Vue {
  @Prop({
    type: String,
    required: true
  })
  public field!: string;

  @Prop({
    required: true
  })
  public title!: string;

  @Prop({
    type: [Boolean, Object]
  })
  public sortable!: boolean | object;

  @Prop({
    type: Object,
    required: true
  })
  public columnConfig;

  @Prop({
    type: Object,
    required: true
  })
  public grid!: AnkSmartElementGrid;
  public showFilter = false;
  public collision = {
    horizontal: "fit",
    vertical: "flip"
  };
  public filterOffset = {
    top: 0,
    left: 0
  };
  public animate = { closeDuration: 0 };
  public get hasSubtitle(): boolean {
    return this.grid.contextTitles && Array.isArray(this.columnConfig.context) && this.columnConfig.context.length;
  }

  public get subtitle(): string {
    if (this.hasSubtitle) {
      return this.columnConfig.context.join(` ${this.grid.contextTitlesSeparator} `);
    }
    return "";
  }

  public get sortableStateIcon(): string {
    const sortField = this.currentSort;
    if (sortField) {
      switch (sortField.dir) {
        case "asc":
          return `<i class='smart-element-header-sort-button k-icon k-i-sort-asc-sm'></i>`;
        case "desc":
          return `<i class='smart-element-header-sort-button k-icon k-i-sort-desc-sm'></i>`;
      }
    }
    return `<i class='smart-element-header-sort-button smart-element-header-sort-button--none'></i>`;
  }

  public get isFiltered(): boolean {
    return (
      this.grid.currentFilter &&
      !!this.grid.currentFilter.filters.filter(
        (f: kendo.data.DataSourceFilter & { field?: string }) => f.field === this.field
      ).length
    );
  }

  public get isActive(): boolean {
    return !!this.showFilter;
  }

  public showFilters(): void {
    if (!this.showFilter) {
      // Place the correct popup position
      this.setFilterOffset();
    }
    this.showFilter = !this.showFilter;
  }

  public get currentSort(): kendo.data.DataSourceSortItem {
    if (this.grid && this.grid.currentSort) {
      return this.grid.currentSort.find(sort => {
        return sort.field === this.field;
      });
    } else {
      return null;
    }
  }

  protected onSort(): void {
    let nextSortableDir: string;
    if (this.currentSort && this.currentSort.dir) {
      switch (this.currentSort.dir) {
        case "asc":
          nextSortableDir = "desc";
          break;
        case "desc":
          if (this.grid.sorter && this.grid.sorter.allowUnsort === false) {
            nextSortableDir = "asc";
          } else {
            nextSortableDir = null;
          }
          break;
        case null:
          nextSortableDir = "asc";
          break;
      }
    } else {
      nextSortableDir = "asc";
    }
    this.$emit("SortChange", {
      sort: [
        {
          field: this.field,
          dir: nextSortableDir
        }
      ]
    });
  }

  protected clearFilter(...args): void {
    this.$emit("FilterChange", ...args);
    // Close popup when filter is cleared
    this.showFilter = false;
  }

  protected filter(...args): void {
    this.$emit("FilterChange", ...args);
    // Close popup when filter is applied
    this.showFilter = false;
  }
  protected setFilterOffset(): void {
    const filter = $(this.$refs.filterButton).offset();
    if (!filter || !filter.top || !filter.left) {
      return;
    }
    const left = filter.left - 200 <= 0 ? 28 : filter.left - 200;
    this.filterOffset = {
      top: filter.top + 18,
      left
    };
  }

  public created(): void {
    // Enable disappear popup if click outside the popup
    $(window).on(`mouseup.popupGrid${this._uid}`, event => {
      const target = event.originalEvent.target as Element;
      if (this.$refs.filterPopup) {
        const container = this.$refs.filterPopup as Vue;
        const containerElement = $(container.$el);
        // If container is visible and click target is not contained by the container so it's a outside click
        if (containerElement.is(":visible") && !containerElement.is(target) && !$.contains(container.$el, target)) {
          this.showFilter = false;
        }
      }
    });
  }

  public mounted(): void {
    $(window).on(`resize.popupGrid${this._uid}`, () => {
      this.setFilterOffset();
    });
  }

  public beforeDestroy(): void {
    $(window).off(`.popupGrid${this._uid}`);
  }
}
