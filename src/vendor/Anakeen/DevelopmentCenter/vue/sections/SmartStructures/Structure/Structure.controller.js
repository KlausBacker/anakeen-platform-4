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
  name: "structure",
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
      return `/api/v2/devel/smart/structures/${this.ssName}/fields/`;
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
          return response.data.data.fields;
        },
        model: {
          id: "id",
          parentId: "parentId",
          expanded: true
        },
        sort: [{ field: "displayOrder", order: "asc" }]
      }
    });
    $(window).resize(() => {
      if (this.$refs.structureTree) {
        this.$refs.structureTree.kendoWidget().resize();
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
        const that = this;
        items.each(function addTypeClass() {
          let dataItem = tree.dataItem(this);
          if (dataItem.type) {
            $(this).addClass(" attr-type--" + dataItem.type);
          }
          if (dataItem.structure !== that.ssName) {
            $(this).addClass(" is-herited");
          }
          if (dataItem.declaration === "overrided") {
            $(this).addClass(" is-overrided");
          }
        });
      }, 1);
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
            str += `<li><span><b>${item}</b></span> : <span>${
              dataItem[colId][item]
            }</span></li>`;
          });
          return str;
        }
        let className = "";
        if (
          colId !== "overrides" &&
          dataItem["declaration"] === "overrided" &&
          dataItem["overrides"]
        ) {
          Object.keys(dataItem["overrides"].toJSON()).forEach(item => {
            if (item === colId) {
              className = "overrided";
            }
          });
        }
        if (className) {
          return `<div class="${className}">${dataItem[colId]}</div>`;
        }
        return dataItem[colId];
      };
    },
    refreshStructure() {
      this.$refs.structureTree.kendoWidget().dataSource.filter({});
      this.$refs.structureTree.kendoWidget().dataSource.read();
    }
  }
};
