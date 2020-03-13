import { Component, Prop, Vue } from "vue-property-decorator";
import AnkGridFilter from "./AnkGridFilter/AnkGridFilter.vue";
import AnkSmartElementGrid from "../AnkSEGrid.component";
import { Popup } from "@progress/kendo-vue-popup";
import $ from "jquery";

enum SortableDirection {
  NONE,
  ASC,
  DESC
}

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
  public sortableDir: SortableDirection = SortableDirection.NONE;
  public showFilter = false;
  public collision = {
    horizontal: "fit",
    vertical: "flip"
  };
  public filterOffset = {
    top: 0,
    left: 0
  };
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
    switch (this.sortableDir) {
      case SortableDirection.NONE:
        return `<i class='smart-element-header-sort-button smart-element-header-sort-button--none'></i>`;
      case SortableDirection.ASC:
        return `<i class='smart-element-header-sort-button k-icon k-i-sort-asc-sm'></i>`;
      case SortableDirection.DESC:
        return `<i class='smart-element-header-sort-button k-icon k-i-sort-desc-sm'></i>`;
    }
  }

  public get isFiltered(): boolean {
    return (
      this.grid.currentFilter &&
      !!this.grid.currentFilter.filters.filter(
        (f: kendo.data.DataSourceFilter & { field?: string }) => f.field === this.field
      ).length
    );
  }
  public showFilters(): void {
    this.showFilter = !this.showFilter;
  }
  public created(): void {
    // sort change
    if (this.grid.sorter && this.grid.sorter.allowUnsort === false && this.sortable) {
      this.sortableDir = SortableDirection.ASC;
      this.$emit("SortChange", {
        sort: [
          {
            field: this.field,
            dir: "asc"
          }
        ]
      });
    }
  }
  protected onSort(): void {
    let sortableStr: string;
    const sortableValues = [null, "asc", "desc"];
    this.sortableDir = (this.sortableDir + 1) % 3;
    if (this.grid.sorter && this.grid.sorter.allowUnsort === true && this.sortable) {
      sortableStr = sortableValues[this.sortableDir];
    } else {
      if (this.sortableDir === 0) {
        this.sortableDir = (this.sortableDir + 1) % 3;
      }
      sortableStr = sortableValues[this.sortableDir];
    }
    this.$emit("SortChange", {
      sort: [
        {
          field: this.field,
          dir: sortableStr
        }
      ]
    });
  }

  protected clearFilter(...args): void {
    this.$emit("FilterChange", ...args);
  }

  protected filter(...args): void {
    this.$emit("FilterChange", ...args);
  }
  protected setFilterOffset(): void {
    const filter = $(this.$refs.filterButton).offset();
    if ($(this.$refs.filterButton).is(":hidden")) {
      this.showFilter = false;
    }
    if (!filter || !filter.top || !filter.left) {
      return;
    }
    const left = filter.left - 200 <= 0 ? 28 : filter.left - 200;
    this.filterOffset = {
      top: filter.top + 18,
      left
    };
  }

  public mounted(): void {
    this.setFilterOffset();
    $(window).on(`resize.popupGrid${this._uid}`, () => {
      this.setFilterOffset();
    });
  }

  public beforeDestroy(): void {
    $(window).off(`.popupGrid${this._uid}`);
  }
}
