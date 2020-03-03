import { Component, Prop, Mixins, Watch } from "vue-property-decorator";
import "@progress/kendo-ui/js/kendo.dropdownlist.js";
import I18nMixin from "../../../mixins/AnkVueComponentMixin/I18nMixin";
import AnkSmartElementGrid, { SmartGridPageSize } from "../AnkSEGrid.component";

@Component({
  name: "ank-grid-pager"
})
export default class GridPager extends Mixins(I18nMixin) {
  @Prop({
    required: true
  })
  public gridComponent!: AnkSmartElementGrid;

  @Prop({
    type: Number,
    default: 0
  })
  public buttonCount!: number;

  @Prop({
    type: Boolean,
    default: true
  })
  public info!: boolean;

  @Prop({
    type: [Boolean, Array],
    default: () => [10, 20, 50]
  })
  public pageSizes!: boolean | number[];

  @Prop({
    type: Boolean,
    default: true
  })
  public previousNext!: boolean;

  @Prop({
    type: [Boolean, String],
    default: () => "input"
  })
  public type!: boolean | string;

  @Watch("gridComponent.currentPage.take")
  protected onGridTakeChange(newValue): void {
    if (newValue !== this.pageSize) {
      this.pageSize = newValue;
    }
  }

  @Watch("pageSize")
  protected onPageSizeChange(newValue): void {
    if (this.gridComponent) {
      this.gridComponent.$emit("pageChange", {
        page: {
          skip: 0,
          take: newValue,
          total: this.gridComponent.currentPage.total
        }
      });
    }
  }
  public pageSize: number = Array.isArray(this.pageSizes) && this.pageSizes.length ? this.pageSizes[0] : 10;

  public get total(): number {
    if (this.gridComponent) {
      return this.gridComponent.currentPage.total;
    }
    return 0;
  }

  public get beginPage(): number {
    if (this.gridComponent) {
      return this.gridComponent.currentPage.skip + 1;
    }
    return 0;
  }

  public get endPage(): number {
    if (this.gridComponent) {
      const endPage = this.gridComponent.currentPage.skip + this.pageSize;
      if (endPage > this.gridComponent.currentPage.total) {
        return this.gridComponent.currentPage.total;
      }
      return endPage;
    }
    return 0;
  }

  public get hasPrevious(): boolean {
    if (this.gridComponent) {
      return this.gridComponent.currentPage.skip >= this.pageSize;
    }
    return false;
  }

  public get hasNext(): boolean {
    if (this.gridComponent) {
      return this.gridComponent.currentPage.skip < this.gridComponent.currentPage.total - this.pageSize;
    }
    return false;
  }

  public get hasPreviousNumbersList(): boolean {
    if (this.gridComponent && this.buttonCount) {
      let result = Math.floor(this.gridComponent.currentPage.total / this.pageSize);
      if (this.gridComponent.currentPage.total > result * this.pageSize) {
        result = result + 1;
      }
      if (this.buttonCount >= result) {
        return false;
      } else {
        const currentPage = Math.floor(this.gridComponent.currentPage.skip / this.pageSize);
        const coeff = Math.floor(currentPage / this.buttonCount);
        return !!coeff;
      }
    }
    return false;
  }

  public get hasNextNumbersList(): boolean {
    if (this.gridComponent && this.buttonCount) {
      let result = Math.floor(this.gridComponent.currentPage.total / this.pageSize);
      if (this.gridComponent.currentPage.total > result * this.pageSize) {
        result = result + 1;
      }
      if (this.buttonCount >= result) {
        return false;
      } else {
        const currentPage = Math.floor(this.gridComponent.currentPage.skip / this.pageSize);
        const coeff = Math.floor(currentPage / this.buttonCount);
        return coeff < this.buttonCount;
      }
    }
    return false;
  }

  public get maxButtonsPages(): number | number[] {
    if (this.gridComponent && this.buttonCount) {
      let result = Math.floor(this.gridComponent.currentPage.total / this.pageSize);
      if (this.gridComponent.currentPage.total > result * this.pageSize) {
        result = result + 1;
      }
      if (this.buttonCount >= result) {
        return result;
      } else {
        const pages = [];
        const currentPage = Math.floor(this.gridComponent.currentPage.skip / this.pageSize);
        const coeff = Math.floor(currentPage / this.buttonCount);
        for (let i = 1; i <= this.buttonCount; i++) {
          pages.push(this.buttonCount * coeff + i);
        }
        return pages;
      }
    }
    return 0;
  }
  public mounted(): void {
    const dropdownPageSizes = $(".smart-element-grid-pager-sizes--dropdown");
    dropdownPageSizes
      .kendoDropDownList({
        dataSource: this.pageSizes,
        change: () => {
          this.pageSize = Number(dropdownPageSizes.val());
        }
      })
      .data("kendoDropDownList");
  }
  public goToPage(pageNumber): void {
    if (this.gridComponent) {
      this.gridComponent.$emit("pageChange", {
        page: {
          skip: (pageNumber - 1) * this.pageSize,
          take: this.pageSize,
          total: this.gridComponent.currentPage.total
        }
      });
    }
  }

  public getCurrentPage(): SmartGridPageSize {
    const result = {
      page: 0,
      pageSize: 0,
      total: 0
    };
    if (this.gridComponent) {
      result.page = Math.floor(this.gridComponent.currentPage.skip / this.pageSize) + 1;
      result.pageSize = this.pageSize;
      result.total = this.gridComponent.currentPage.total;
    }
    return result;
  }

  public firstPage(): void {
    if (this.gridComponent && this.hasPrevious) {
      this.gridComponent.$emit("pageChange", {
        page: {
          skip: 0,
          take: this.pageSize,
          total: this.gridComponent.currentPage.total
        }
      });
    }
  }

  public previousPage(): void {
    if (this.gridComponent && this.hasPrevious) {
      this.gridComponent.$emit("pageChange", {
        page: {
          skip: this.gridComponent.currentPage.skip - this.pageSize,
          take: this.pageSize,
          total: this.gridComponent.currentPage.total
        }
      });
    }
  }

  public nextPage(): void {
    if (this.gridComponent && this.hasNext) {
      this.gridComponent.$emit("pageChange", {
        page: {
          skip: this.gridComponent.currentPage.skip + this.pageSize,
          take: this.pageSize,
          total: this.gridComponent.currentPage.total
        }
      });
    }
  }

  public lastPage(): void {
    if (this.gridComponent && this.hasNext) {
      this.gridComponent.$emit("pageChange", {
        page: {
          skip: this.gridComponent.currentPage.total - this.pageSize,
          take: this.pageSize,
          total: this.gridComponent.currentPage.total
        }
      });
    }
  }

  protected isCurrentPage(pageNumber): boolean {
    if (this.gridComponent) {
      return pageNumber === Math.floor(this.gridComponent.currentPage.skip / this.gridComponent.currentPage.take) + 1;
    }
    return false;
  }

  protected previousNumbersList(): void {
    const maxPages = this.maxButtonsPages as number[];
    this.goToPage(maxPages[0] - 1);
  }

  protected nextNumbersList(): void {
    const maxPages = this.maxButtonsPages as number[];
    this.goToPage(maxPages[maxPages.length - 1] + 1);
  }
}
