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
  name: "parameters",
  props: ["ssName"],
  components: {
    "kendo-treelist": TreeList
  },
  watch: {
    ssName(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.remoteDataSource.read();
      }
    }
  },
  data() {
    return {
      remoteDataSource: ""
    };
  },
  computed: {
    url() {
      return `/api/v2/devel/smart/structures/${this.ssName}/parameters/`;
    }
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
          return response.data.data.parameterValues;
        },
        model: {
          id: "id",
          parentId: "parentId",
          expanded: true
        }
      }
    });
    $(window).resize(() => {
      if (this.$refs.parametersTree) {
        this.$refs.parametersTree.kendoWidget().resize();
      }
    });
  },
  methods: {
    onDataBound(e) {
      let tree = e.sender;
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
        items.each(function addTypeClass() {
          let dataItem = tree.dataItem(this);
          if (dataItem.type) {
            $(this).addClass(" attr-type--" + dataItem.type);
          }
          if (dataItem.overrides && dataItem.declaration === "overrided") {
            $(this).addClass(" is-overrided");
          }
        });
      }, 1);
    },
    refreshParameters() {
      this.$refs.parametersTree.kendoWidget().dataSource.filter({});
      this.$refs.parametersTree.kendoWidget().dataSource.read();
    },
    columnTemplate(colId) {
      return dataItem => {
        if (dataItem[colId] === null || dataItem[colId] === undefined) {
          return "";
        }
        if (
          dataItem[colId] &&
          (colId === "optionValues" || colId === "properties")
        ) {
          let str = "";
          Object.keys(dataItem[colId].toJSON()).forEach(item => {
            str += "<li>" + item + "</li>";
          });
          return str;
        }
        return dataItem[colId];
      };
    }
  }
};