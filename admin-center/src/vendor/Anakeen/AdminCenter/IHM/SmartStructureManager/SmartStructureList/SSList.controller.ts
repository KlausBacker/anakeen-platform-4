import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import { Component, Prop, Vue, Watch, Mixins } from "vue-property-decorator";

Vue.use(DataSourceInstaller);
@Component({
  model: {
    event: "structureSelected",
    prop: "selected"
  }
})
export default class SmartStructureListController extends Mixins(AnkI18NMixin) {
  @Prop({
    default: true,
    type: [Boolean, Object]
  })
  public filter;
  @Prop({
    default: "",
    type: String
  })
  public selected: string = "";
  public listModel: object = {
    id: "id"
  };
  public listFilter: string = "";
  public dataSource = null;
  public clearFilter() {
    this.listFilter = "";
  }
  @Watch("listFilter")
  public watchListFilter(newValue, oldValue) {
    if (newValue !== oldValue) {
      this.filterList(newValue);
    }
  }
  public onListItemClicked(ssId) {
    this.$parent.$emit("structureSelected", `${ssId}`);
  }

  public hasFilter() {
    return !!this.filter;
  }

  public get translations() {
    return {
      listFilterPlaceholder: this.$t("AdminCenterSmartStructure.Search a structure")
    };
  }
  public get isEmpty() {
    return !this.tabs.length;
  }
  public get tabs() {
    if (this.dataSource) {
      const view = this.dataSource.view();
      if (view.length) {
        return view.toJSON();
      }
    }
    return [];
  }
  public get filterPlaceholder() {
    if (this.hasFilter && typeof this.filter === "object") {
      return this.filter.placeholder || this.translations.listFilterPlaceholder;
    }
    return this.translations.listFilterPlaceholder;
  }

  public readData(options) {
    kendo.ui.progress($(this.$refs.ssList as HTMLElement), true);
    this.$http
      .get(`/api/v2/admin/smart-structures/all`, {
        params: options.data,
        paramsSerializer: kendo.jQuery.param
      })
      .then(response => {
        kendo.ui.progress($(this.$refs.ssList as HTMLElement), false);
        options.success(response);
        this.$nextTick(() => {
          this.autoScrollOnSelected();
          this.$emit("ss-list-ready", this.parseData(response));
        });
      })
      .catch(error => {
        kendo.ui.progress($(this.$refs.ssList as HTMLElement), false);
        options.error(error);
      });
  }

  public parseData(response) {
    if (response && response.data && response.data.data) {
      return response.data.data;
    }
    return [];
  }

  public autoScrollOnSelected() {
    const active = $(".item-active", this.$el);
    if (active.length) {
      active[0].scrollIntoView();
    }
  }
  public mounted() {
    // @ts-ignore
    this.dataSource = this.$refs.ssDataSource.kendoWidget();
    this.dataSource.read();
  }

  public filterList(filterValue) {
    if (this.dataSource) {
      let filterObject = {
        filters: [
          {
            field: "title",
            operator: "contains",
            value: filterValue
          },
          {
            field: "name",
            operator: "contains",
            value: filterValue
          }
        ],
        logic: "or"
      };
      if (this.hasFilter && typeof this.filter === "object") {
        if (typeof this.filter.doFilter === "function") {
          filterObject = this.filter.doFilter.call(null, filterValue, filterObject);
        }
      }
      this.dataSource.filter(filterObject);
    }
  }
}
