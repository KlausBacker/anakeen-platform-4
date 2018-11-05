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
  name: "defaultValues",
  props: ["ssName"],
  components: {
    "kendo-treelist": TreeList
  },
  watch: {
    ssName(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.valuesDataSource.read();
      }
    }
  },
  data() {
    return {
      valuesDataSource: ""
    };
  },
  computed: {
    url() {
      return `/api/v2/devel/smart/structures/${this.ssName}/defaults/`;
    }
  },
  mounted() {
    this.valuesDataSource = new kendo.data.TreeListDataSource({
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
          const items = response.data.data.defaultValues;
          const fields = Object.keys(items).map(item => {
            return {
              idVal: item,
              config: items[item].config,
              type: items[item].type,
              value: items[item].value
            };
          });
          return fields;
        }
      }
    });
    $(window).resize(() => {
      if (this.$refs.defaultValuesTree) {
        this.$refs.defaultValuesTree.kendoWidget().resize();
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
    refreshDefaultValues() {
      this.$refs.defaultValuesTree.kendoWidget().dataSource.filter({});
      this.$refs.defaultValuesTree.kendoWidget().dataSource.read();
    },
    columnTemplate(colId) {
      return dataItem => {
        if (dataItem[colId] === null || dataItem[colId] === undefined) {
          return "";
        }
        if (dataItem[colId] && colId === "value") {
          let str = "";
          Object.keys(dataItem[colId].toJSON()).forEach(item => {
            str += "<li>" + dataItem[colId][item] + "</li>";
          });
          return str;
        }
        return dataItem[colId];
      };
    }
  }
};
