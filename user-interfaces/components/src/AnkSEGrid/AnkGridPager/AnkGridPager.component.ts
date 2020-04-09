import { Component, Prop, Mixins, Watch } from "vue-property-decorator";
import "@progress/kendo-ui/js/kendo.dropdownlist.js";
import I18nMixin from "../../../mixins/AnkVueComponentMixin/I18nMixin";
import AnkSmartElementGrid, { SmartGridPageSize } from "../AnkSEGrid.component";
import $ from "jquery";
import GridEvent from "../AnkGridEvent/AnkGridEvent";

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
    default: false
  })
  public showCurrentPage!: boolean | string;

  @Watch("gridComponent.currentPage.take")
  protected onGridTakeChange(newValue): void {
    if (newValue !== this.pageSize) {
      this.pageSize = newValue;
    }
  }

  @Watch("pageSize")
  protected onPageSizeChange(newValue): void {
    if (this.gridComponent) {
      const gridEvent = new GridEvent(
        {
          page: {
            skip: 0,
            take: newValue,
            total: this.gridComponent.currentPage.total
          },
          pages: {
            page: 1,
            total: Math.ceil(this.gridComponent.currentPage.total / newValue),
            size: newValue
          }
        },
        null,
        false
      );
      this.gridComponent.$emit("pageChange", gridEvent);
    }
  }

  @Watch("pageSizes")
  protected onPageSizesChange(newValue): void {
    this.initPageSizesDropdownList();
  }
  public pageSize: number = Array.isArray(this.pageSizes) && this.pageSizes.length ? this.pageSizes[0] : 10;

  public get total(): number {
    if (this.gridComponent) {
      return this.gridComponent.currentPage.total;
    }
    return 0;
  }

  public get totalPage(): number {
    if (this.gridComponent) {
      return Math.ceil(this.gridComponent.currentPage.total / this.pageSize);
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
    this.initPageSizesDropdownList();
  }
  public goToPage(pageNumber): void {
    if (this.gridComponent) {
      const gridEvent = new GridEvent(
        {
          page: {
            skip: (pageNumber - 1) * this.pageSize,
            take: this.pageSize,
            total: this.gridComponent.currentPage.total
          },
          pages: {
            page: pageNumber,
            size: this.pageSize,
            total: Math.ceil(this.gridComponent.currentPage.total / this.pageSize)
          }
        },
        null,
        false
      );
      this.gridComponent.$emit("pageChange", gridEvent);
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
      const gridEvent = new GridEvent(
        {
          page: {
            skip: 0,
            take: this.pageSize,
            total: this.gridComponent.currentPage.total
          },
          pages: {
            page: 1,
            size: this.pageSize,
            total: Math.ceil(this.gridComponent.currentPage.total / this.pageSize)
          }
        },
        null,
        false
      );
      this.gridComponent.$emit("pageChange", gridEvent);
    }
  }

  public previousPage(): void {
    if (this.gridComponent && this.hasPrevious) {
      const gridEvent = new GridEvent(
        {
          page: {
            skip: this.gridComponent.currentPage.skip - this.pageSize,
            take: this.pageSize,
            total: this.gridComponent.currentPage.total
          },
          pages: {
            page: Math.ceil((this.gridComponent.currentPage.skip - this.pageSize) / this.pageSize) + 1,
            size: this.pageSize,
            total: Math.ceil(this.gridComponent.currentPage.total / this.pageSize)
          }
        },
        null,
        false
      );
      this.gridComponent.$emit("pageChange", gridEvent);
    }
  }

  public nextPage(): void {
    if (this.gridComponent && this.hasNext) {
      const gridEvent = new GridEvent(
        {
          page: {
            skip: this.gridComponent.currentPage.skip + this.pageSize,
            take: this.pageSize,
            total: this.gridComponent.currentPage.total
          },
          pages: {
            page: Math.ceil((this.gridComponent.currentPage.skip + this.pageSize) / this.pageSize) + 1,
            size: this.pageSize,
            total: Math.ceil(this.gridComponent.currentPage.total / this.pageSize)
          }
        },
        null,
        false
      );
      this.gridComponent.$emit("pageChange", gridEvent);
    }
  }

  public lastPage(): void {
    if (this.gridComponent && this.hasNext) {
      const gridEvent = new GridEvent(
        {
          page: {
            skip: this.gridComponent.currentPage.total - this.pageSize,
            take: this.pageSize,
            total: this.gridComponent.currentPage.total
          },
          pages: {
            page: Math.ceil((this.gridComponent.currentPage.total - this.pageSize) / this.pageSize) + 1,
            size: this.pageSize,
            totall: Math.ceil(this.gridComponent.currentPage.total / this.pageSize)
          }
        },
        null,
        false
      );
      this.gridComponent.$emit("pageChange", gridEvent);
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

  protected initPageSizesDropdownList(): void {
    if (this.pageSizes) {
      const dropdownPageSizes = $(this.$refs.gridPageSizes);
      dropdownPageSizes
        .kendoDropDownList({
          dataSource: this.pageSizes,
          popup: {
            // @ts-ignore
            appendTo: $(this.$refs.gridPagerContainer)
          },
          change: () => {
            this.pageSize = Number(dropdownPageSizes.val());
          }
        })
        .data("kendoDropDownList");
    }
  }
}
