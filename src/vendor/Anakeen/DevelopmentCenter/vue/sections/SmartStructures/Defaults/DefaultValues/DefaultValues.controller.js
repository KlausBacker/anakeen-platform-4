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
  name: "defaultFields",
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
      return `/api/v2/devel/smart/structures/${this.ssName}/defaults/`;
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
          return this.getDefaultValues(response.data.data);
        },
        model: {
          id: "id",
          parentId: "parentId",
          expanded: true
        }
      }
    });
    $(window).resize(() => {
      if (this.$refs.defaultFieldsTree) {
        this.$refs.defaultFieldsTree.kendoWidget().resize();
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
    refreshDefaultFields() {
      this.$refs.defaultFieldsTree.kendoWidget().dataSource.filter({});
      this.$refs.defaultFieldsTree.kendoWidget().dataSource.read();
    },
    columnTemplate(colId) {
      return dataItem => {
        if (dataItem[colId] === null || dataItem[colId] === undefined) {
          return "";
        }
        if (dataItem[colId].length > 1 && colId === "value") {
          let str = "";
          Object.keys(dataItem[colId].toJSON()).forEach(item => {
            str += "<li>" + dataItem[colId][item] + "</li>";
          });
          return str;
        } else if (dataItem[colId].length <= 1 && colId === "value") {
          return dataItem[colId][0];
        }
        return dataItem[colId];
      };
    },
    getDefaultValues(response) {
      const items = response.defaultValues;
      const fields = Object.keys(items).map(item => {
        return {
          idVal: item,
          config: items[item].config,
          type: items[item].type,
          value: items[item].value
        };
      });
      fields.forEach(items2 => {
        Object.keys(response.fields).forEach(items => {
          if (items2.type === "field") {
            if (items2.idVal === response.fields[items].id) {
              response.fields[items].config = items2.config;
              response.fields[items].value = items2.value;
            }
          }
        });
      });
      return response.fields;
    }
  }
};