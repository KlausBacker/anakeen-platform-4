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
    }
  },
  components: {
    "kendo-treelist": TreeList
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
          this.$http
            .get(this.url)
            .then(options.success)
            .catch(options.error);
        }
      },
      schema: {
        data: response => {
          return this.getValues(response.data.data);
        },
        model: {
          id: "id",
          parentId: "parentId",
          expanded: true
        }
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
    JSON.parse(columns).forEach(item => {
      if (item.hidden) {
        this.$refs.ssTreelist.kendoWidget().hideColumn(item.field);
      } else {
        this.$refs.ssTreelist.kendoWidget().showColumn(item.field);
      }
    });
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
    },
    refreshTree() {
      this.$refs.ssTreelist.kendoWidget().dataSource.filter({});
      this.$refs.ssTreelist.kendoWidget().dataSource.read();
    }
  }
};
