import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import GridController from "../AnkSEGrid.component";
import { Popup } from "@progress/kendo-vue-popup";
import AnkGridFilter from "./AnkGridFilter/AnkGridFilter.vue";

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
export default class GridFilterCell extends Vue {
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
    type: [Boolean, Object],
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
  public grid!: GridController;

  public sortableDir: SortableDirection = SortableDirection.NONE;
  public showFilter: boolean = false;

  public get hasSubtitle() {
    return this.grid.contextTitles && Array.isArray(this.columnConfig.context) && this.columnConfig.context.length;
  }

  public get subtitle() {
    if (this.hasSubtitle) {
      return this.columnConfig.context.join(` ${this.grid.contextTitlesSeparator} `);
    }
    return "";
  }

  public get sortableStateIcon() {
    switch (this.sortableDir) {
      case SortableDirection.NONE:
        return `<i class='smart-element-header-sort-button smart-element-header-sort-button--none'></i>`;
      case SortableDirection.ASC:
        return `<i class='smart-element-header-sort-button k-icon k-i-sort-asc-sm'></i>`;
      case SortableDirection.DESC:
        return `<i class='smart-element-header-sort-button k-icon k-i-sort-desc-sm'></i>`;
    }
  }

  public get isFiltered() {
    return this.grid.currentFilter && !!this.grid.currentFilter.filters.filter(f => f.field === this.field).length;
  }

  public created() {
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

  protected onSort() {
    let sortableStr = "";
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

  protected clearFilter(...args) {
    this.showFilter = false;
    this.$emit("filterchange", ...args);
  }

  protected filter(...args) {
    this.showFilter = false;
    this.$emit("filterchange", ...args);
  }
}
