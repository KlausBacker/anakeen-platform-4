import Vue from "vue";
import "@progress/kendo-ui";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import {
  TreeList,
  TreeListInstaller
} from "@progress/kendo-treelist-vue-wrapper";

Vue.use(DataSourceInstaller);
Vue.use(TreeListInstaller);

export default {
  name: "ss-treelist",
  props: {
    messages: {
      type: String,
      default: () => "There are no data for this SmartStructure"
    },
    ssName: {
      type: String,
      default: () => ""
    },
    items: {
      type: Array,
      default: () => []
    },
    filterable: {
      default: () => ({
        extra: false,
        operators: { string: { contains: "Contains" } }
      })
    },
    sortable: {
      default: false
    },
    url: {
      type: String,
      default: () => ""
    },
    getValues: {
      default: () => {
        return {};
      }
    },
    columnTemplate: {
      default: () => {
        return {};
      }
    },
    headerTemplate: {
      default: () => column => column.label || column.name
    },
    model: {
      default: () => ({
        id: "id",
        parentId: "parentId",
        expanded: true
      })
    }
  },
  components: {
    "kendo-treelist": TreeList
  },
  watch: {
    url() {
      if (this.remoteDataSource) {
        this.remoteDataSource.read();
      }
    }
  },
  data() {
    return {
      remoteDataSource: ""
    };
  },
  mounted() {
    this.remoteDataSource = new kendo.data.TreeListDataSource({
      transport: {
        read: options => {
          kendo.ui.progress(this.$(".k-grid-content", this.$el), true);
          this.$http
            .get(this.url)
            .then(response => {
              kendo.ui.progress(this.$(".k-grid-content", this.$el), false);
              options.success(response);
            })
            .catch(error => {
              kendo.ui.progress(this.$(".k-grid-content", this.$el), false);
              console.error(error);
              options.error(error);
            });
        }
      },
      schema: {
        data: response => {
          return this.getValues(response.data.data);
        },
        model: this.model
      }
    });
    $(window).resize(() => {
      if (this.$refs.ssTreelist) {
        this.$refs.ssTreelist.kendoWidget().resize();
      }
    });
    const columns = window.localStorage.getItem(
      "ss-list-column-conf-" + this.$route.name
    );
    if (columns) {
      JSON.parse(columns).forEach(item => {
        if (item.hidden) {
          this.$refs.ssTreelist.kendoWidget().hideColumn(item.field);
        } else {
          this.$refs.ssTreelist.kendoWidget().showColumn(item.field);
        }
      });
    }
  },
  methods: {
    onColumnHide() {
      window.localStorage.setItem(
        "ss-list-column-conf-" + this.$route.name,
        JSON.stringify(this.$refs.ssTreelist.kendoWidget().columns)
      );
    },
    onColumnShow() {
      window.localStorage.setItem(
        "ss-list-column-conf-" + this.$route.name,
        JSON.stringify(this.$refs.ssTreelist.kendoWidget().columns)
      );
    },
    onDataBound(e) {
      let tree = e.sender;
      this.removeRowClassName(tree);
      this.addRowClassName(tree);
      tree.autoFitColumn(1);
      this.$emit("tree-list-data-bound", e);
    },
    onExpand(e) {
      let tree = e.sender;
      this.addRowClassName(tree);
    },
    onCollapse(e) {
      let tree = e.sender;
      this.addRowClassName(tree);
    },
    addRowClassName(tree) {
      let items = tree.items();
      window.setTimeout(() => {
        const that = this;
        items.each(function addTypeClass() {
          let dataItem = tree.dataItem(this);
          if (dataItem.type) {
            $(this).addClass(" attr-type--" + dataItem.type);
          }
          if (
            dataItem.structure !== that.ssName &&
            that.$route.name === "SmartStructures::fields::structure"
          ) {
            $(this).addClass(" is-herited");
          }
          if (
            dataItem.declaration === "overrided" &&
            that.$route.name === "SmartStructures::fields::structure"
          ) {
            $(this).addClass(" is-overrided");
          }
          if (dataItem.parentId) {
            if (
              that.$refs.ssTreelist
                .kendoWidget()
                .dataSource.get(dataItem.parentId).type === "array"
            ) {
              $(this).addClass(" is-array-children");
            }
          }
        });
      }, 1);
    },
    removeRowClassName(tree) {
      let items = tree.items();
      window.setTimeout(() => {
        items.each(function removeTypeClass() {
          let dataItem = tree.dataItem(this);
          if (
            $(this)
              .attr("class")
              .includes(" attr-type--")
          ) {
            $(this).removeClass(" attr-type--" + dataItem.type);
          }
          if (
            $(this)
              .attr("class")
              .includes(" is-herited")
          ) {
            $(this).removeClass(" is-herited");
          }
          if (
            $(this)
              .attr("class")
              .includes(" is-overrided")
          ) {
            $(this).removeClass(" is-overrided");
          }
        });
      }, 1);
    }
  }
};