import { Component, Prop, Vue, Watch } from "vue-property-decorator";
import { process } from "@progress/kendo-data-query";
import { Grid } from "@progress/kendo-vue-grid";
import { IDomainConfig } from "./IDomainConfigType";
// declare var $;
// declare var kendo;

// noinspection JSUnusedGlobalSymbols
@Component({
  name: "ank-fullsearch-list",

  components: {
    "kendo-grid": Grid
  }
})
export default class FullsearchConfigController extends Vue {
  public domains: IDomainConfig[] = [];

  public gridData;

  public group = [{ field: "UnitsInStock" }];
  public products = this.createRandomData(1000);
  public columns = [
    { field: "ProductID", filterable: false, title: "ID", width: "50px" },
    { field: "ProductName", title: "Product Name" },
    { field: "UnitPrice", filter: "numeric", title: "Unit Price" },
    { field: "UnitsInStock", title: "Units In Stock" }
  ];
  public expandedItems: [];

  public created(): void {
    this.getData();
  }

  public mounted(): void {
    this.fetchConfigs();
  }
  protected fetchConfigs(): void {
    this.$http.get("/api/admin/fullsearch/domains/").then(response => {
      this.domains = response.data.data.config;
    });
  }
  protected getData(): void {
    this.gridData = process(this.products, { skip: 0, group: this.group });
  }

  public expandChange(event): void {
    Vue.set(event.dataItem, event.target.$props.expandField, event.value);
  }
  protected createRandomData(count): unknown[] {
    const productNames = ["Chai", "Chang", "Syrup", "Apple", "Orange", "Banana", "Lemon", "Pineapple", "Tea", "Milk"];
    const unitPrices = [12.5, 10.1, 5.3, 7, 22.53, 16.22, 20, 50, 100, 120];
    const units = [2, 7, 12, 25, 67, 233, 123, 53, 67, 89];

    return Array(count)
      .fill({})
      .map((_, idx) => ({
        ProductID: idx + 1,
        ProductName: productNames[Math.floor(Math.random() * productNames.length)],
        UnitPrice: unitPrices[Math.floor(Math.random() * unitPrices.length)],
        UnitsInStock: units[Math.floor(Math.random() * units.length)]
      }));
  }
}
