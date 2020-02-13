import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import GridController from "../AnkSEGrid.component";
import { DropDownList } from "@progress/kendo-vue-dropdowns";

@Component({
  name: "ank-grid-pager",
  components: {
    DropDownList
  }
})
export default class AnkGridCellHtmlText extends Vue {
  @Prop({
    required: true
  })
  public gridComponent!: GridController;

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
  protected onGridTakeChange(newValue) {
    if (newValue !== this.pageSize) {
      this.pageSize = newValue;
    }
  }

  @Watch("pageSize")
  protected onPageSizeChange(newValue) {
    if (this.gridComponent) {
      this.gridComponent.$emit("gridPageChange", {
        page: {
          skip: 0,
          take: newValue,
          total: this.gridComponent.currentPage.total
        }
      });
    }
  }

  public pageSize: number = Array.isArray(this.pageSizes) && this.pageSizes.length ? this.pageSizes[0] : 10;

  public get total() {
    if (this.gridComponent) {
      return this.gridComponent.currentPage.total;
    }
    return 0;
  }

  public get beginPage() {
    if (this.gridComponent) {
      return this.gridComponent.currentPage.skip + 1;
    }
    return 0;
  }

  public get endPage() {
    if (this.gridComponent) {
      const endPage = this.gridComponent.currentPage.skip + this.pageSize;
      if (endPage > this.gridComponent.currentPage.total) {
        return this.gridComponent.currentPage.total;
      }
      return endPage;
    }
    return 0;
  }

  public get hasPrevious() {
    if (this.gridComponent) {
      return this.gridComponent.currentPage.skip >= this.pageSize;
    }
    return false;
  }

  public get hasNext() {
    if (this.gridComponent) {
      return this.gridComponent.currentPage.skip < this.gridComponent.currentPage.total - this.pageSize;
    }
    return false;
  }

  public get hasPreviousNumbersList() {
    if (this.gridComponent && this.buttonCount) {
      let result = Math.floor(this.gridComponent.currentPage.total / this.pageSize);
      if (this.gridComponent.currentPage.total > result * this.pageSize) {
        result = result + 1;
      }
      if (this.buttonCount >= result) {
        return false;
      } else {
        const pages = [];
        const currentPage = Math.floor(this.gridComponent.currentPage.skip / this.pageSize);
        const coeff = Math.floor(currentPage / this.buttonCount);
        return !!coeff;
      }
    }
    return false;
  }

  public get hasNextNumbersList() {
    if (this.gridComponent && this.buttonCount) {
      let result = Math.floor(this.gridComponent.currentPage.total / this.pageSize);
      if (this.gridComponent.currentPage.total > result * this.pageSize) {
        result = result + 1;
      }
      if (this.buttonCount >= result) {
        return false;
      } else {
        const pages = [];
        const currentPage = Math.floor(this.gridComponent.currentPage.skip / this.pageSize);
        const coeff = Math.floor(currentPage / this.buttonCount);
        return coeff < this.buttonCount;
      }
    }
    return false;
  }

  public get maxButtonsPages() {
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

  public goToPage(pageNumber) {
    if (this.gridComponent) {
      this.gridComponent.$emit("gridPageChange", {
        page: {
          skip: (pageNumber - 1) * this.pageSize,
          take: this.pageSize,
          total: this.gridComponent.currentPage.total
        }
      });
    }
  }

  public getCurrentPage() {
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

  public firstPage() {
    if (this.gridComponent && this.hasPrevious) {
      this.gridComponent.$emit("gridPageChange", {
        page: {
          skip: 0,
          take: this.pageSize,
          total: this.gridComponent.currentPage.total
        }
      });
    }
  }

  public previousPage() {
    if (this.gridComponent && this.hasPrevious) {
      this.gridComponent.$emit("gridPageChange", {
        page: {
          skip: this.gridComponent.currentPage.skip - this.pageSize,
          take: this.pageSize,
          total: this.gridComponent.currentPage.total
        }
      });
    }
  }

  public nextPage() {
    if (this.gridComponent && this.hasNext) {
      this.gridComponent.$emit("gridPageChange", {
        page: {
          skip: this.gridComponent.currentPage.skip + this.pageSize,
          take: this.pageSize,
          total: this.gridComponent.currentPage.total
        }
      });
    }
  }

  public lastPage() {
    if (this.gridComponent && this.hasNext) {
      this.gridComponent.$emit("gridPageChange", {
        page: {
          skip: this.gridComponent.currentPage.total - this.pageSize,
          take: this.pageSize,
          total: this.gridComponent.currentPage.total
        }
      });
    }
  }

  protected isCurrentPage(pageNumber) {
    if (this.gridComponent) {
      return pageNumber === Math.floor(this.gridComponent.currentPage.skip / this.gridComponent.currentPage.take) + 1;
    }
    return false;
  }

  protected previousNumbersList() {
    const maxPages = this.maxButtonsPages as number[];
    this.goToPage(maxPages[0] - 1);
  }

  protected nextNumbersList() {
    const maxPages = this.maxButtonsPages as number[];
    this.goToPage(maxPages[maxPages.length - 1] + 1);
  }
}
