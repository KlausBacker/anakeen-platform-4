import { Component, Prop, Vue } from "vue-property-decorator";
import { Popup } from "@progress/kendo-vue-popup";
import AnkGridFilter from "./AnkGridFilter/AnkGridFilter.vue";
import AnkSmartElementGrid from "../AnkSEGrid.component";

enum SortableDirection {
  NONE,
  ASC,
  DESC
}

@Component({
  name: "ank-se-grid-header-cell",
  components: {
    Popup,
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
  public hoverPopup = false;
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
    return this.grid.currentFilter && !!this.grid.currentFilter.filters.filter(f => f.field === this.field).length;
  }

  public created(): void {
    window.addEventListener("click", e => {
      // remove filter's popup when clicking outside of the popup
      const tabFilterClasses = [];
      const popup = $(".smart-element-grid-filter-criteria");
      const popupClasses = $(e.target).attr("class");
      if (popupClasses) {
        if (popupClasses.split(" ")) {
          popupClasses.split(" ").forEach(item => {
            if (popup.find("." + item).length) {
              tabFilterClasses.push(item);
            }
          });
        } else {
          if (popup.find("." + $(e.target).attr("class")).length) {
            tabFilterClasses.push($(e.target).attr("class"));
          }
        }
        if (tabFilterClasses.length === 0) {
          this.showFilter = false;
        }
      } else {
        this.showFilter = false;
      }
    });
    // sort change
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
  public beforeDestroy(): void {
    window.removeEventListener("click", () => {
      if (!this.hoverPopup) {
        this.showFilter = false;
      }
    });
  }
  public clickFilter(): void {
    this.showFilter = !this.showFilter;
    this.hoverPopup = true;
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
