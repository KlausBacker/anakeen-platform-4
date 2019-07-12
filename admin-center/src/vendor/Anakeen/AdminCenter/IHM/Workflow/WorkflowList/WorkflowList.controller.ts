import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import Vue from "vue";
import { Component, Prop } from "vue-property-decorator";

Vue.use(DataSourceInstaller);
declare var kendo;
@Component
export default class WorkflowListController extends Vue {
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

  public onListItemClicked(tab) {
    this.$emit("workflow-clicked", tab);
  }

  public hasFilter() {
    return !!this.filter;
  }

  public get translations() {
    return {
      listFilterPlaceholder: "Search a structure"
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
    kendo.ui.progress($(this.$refs.ssWflList), true);
    this.$http
      .get(`/api/v2/admin/workflow/list/`)
      .then(response => {
        kendo.ui.progress($(this.$refs.ssWflList), false);
        options.success(response);
        this.$nextTick(() => {
          this.autoScrollOnSelected();
          this.$emit("wfl-list-ready", this.parseData(response));
        });
      })
      .catch(error => {
        kendo.ui.progress($(this.$refs.ssWflList), false);
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
    this.dataSource = this.$refs.wflDataSource.kendoWidget();
    this.dataSource.read();
  }
}
