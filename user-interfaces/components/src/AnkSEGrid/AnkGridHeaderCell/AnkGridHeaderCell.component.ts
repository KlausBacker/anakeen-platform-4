import { Component, Prop, Vue } from "vue-property-decorator";
import AnkGridFilter from "./AnkGridFilter/AnkGridFilter.vue";
import AnkSmartElementGrid from "../AnkSEGrid.component";
import "@progress/kendo-ui/js/kendo.popup.js";

enum SortableDirection {
  NONE,
  ASC,
  DESC
}

@Component({
  name: "ank-se-grid-header-cell",
  components: {
    AnkGridFilter
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
      this.grid.currentFilter && !!this.grid.currentFilter.filters.filter((f: any) => f.field === this.field).length
    );
  }
  public showFilters(): void {
    this.showFilter = !this.showFilter;
    const popup = $(".smart-element-grid-filter-content", this.$el).data("kendoPopup");
    if (popup) {
      if (this.showFilter) {
        // $(".smart-element-grid-filter-icon", this.$el).toggleClass("k-state-filter-button-active");
        popup.toggle();
      } else {
        // $(".smart-element-grid-filter-icon", this.$el).toggleClass("k-state-filter-button-active");
        popup.close();
      }
    }
  }
  public mounted(): void {
    $(".smart-element-grid-filter-content", this.$el).kendoPopup({
      anchor: $(this.$refs.filterButton as HTMLElement),
      position: "top right",
      origin: "bottom right",
      appendTo: $(".smart-element-grid-header-content"),
      animation: {
        close: {
          effects: "slideOut zoom:out",
          duration: 300
        },
        open: {
          effects: "slideIn zoom:in",
          duration: 300
        }
      }
    });
  }
  public created(): void {
    // sort change
    // @ts-ignore
    if (this.grid.sorter && this.grid.sorter.allowUnsort === false && this.sortable) {
      this.sortableDir = SortableDirection.ASC;
      this.$emit("sortchange", {
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
    // @ts-ignore
    if (this.grid.sorter && this.grid.sorter.allowUnsort === true && this.sortable) {
      sortableStr = sortableValues[this.sortableDir];
    } else {
      if (this.sortableDir === 0) {
        this.sortableDir = (this.sortableDir + 1) % 3;
      }
      sortableStr = sortableValues[this.sortableDir];
    }
    this.$emit("sortchange", {
      sort: [
        {
          field: this.field,
          dir: sortableStr
        }
      ]
    });
  }

  protected clearFilter(...args): void {
    this.$emit("filterchange", ...args);
  }

  protected filter(...args): void {
    this.$emit("filterchange", ...args);
  }
}
