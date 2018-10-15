import Vue from "vue";
import "@progress/kendo-ui/js/kendo.listview";
import "@progress/kendo-ui/js/kendo.pager";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { ListViewInstaller } from "@progress/kendo-listview-vue-wrapper";

import structureItemVue from "./template/structureItemTemplate.vue";

Vue.use(ListViewInstaller);
Vue.use(DataSourceInstaller);
export default {
  props: {
    filter: {
      type: [Boolean, Object],
      default: true
    },
    position: {
      type: String,
      default: "left",
      validator: value => {
        const validValues = ["left", "right", "top", "bottom"];
        const valid = validValues.indexOf(value) >= 0;
        if (!valid) {
          console.error(
            `Property "position" must be in ${JSON.stringify(validValues)}`
          );
        }
        return valid;
      }
    },
    icon: {
      type: Boolean,
      default: true
    },
    routeName: {
      type: String,
      required: true
    },
    routeParamField: {
      type: String,
      required: true
    },
    smartStructureCategory: {
      type: String,
      default: "all",
      validator: value => {
        const validValues = ["all", "vendor"];
        const valid = validValues.indexOf(value) >= 0;
        if (!valid) {
          console.error(
            `Property "smartStructureCategory" must be in ${JSON.stringify(
              validValues
            )}`
          );
        }
        return valid;
      }
    },
    listUrl: {
      type: String,
      default: "/api/v2/devel/smart/structures/<type>/"
    }
  },
  data() {
    return {
      selectedStructure: null,
      listModel: {
        id: "name"
      },
      listFilter: ""
    };
  },
  watch: {
    listFilter(newValue, oldValue) {
      if (newValue !== oldValue) {
        if (this.$refs.remoteDataSource) {
          if (this.$router.currentRoute.query.filter !== newValue) {
            this.$router.push({ query: { filter: newValue } });
          }
          this.$refs.remoteDataSource.kendoWidget().filter({
            logic: "or",
            filters: [
              {
                field: "name",
                operator: "contains",
                value: newValue
              },
              {
                field: "title",
                operator: "contains",
                value: newValue
              }
            ]
          });
        }
      }
    }
  },
  computed: {
    hasFilter() {
      return !!this.filter;
    },
    resolveListUrl() {
      const baseUrl = this.listUrl;
      if (baseUrl.indexOf("<type>") < -1) {
        return baseUrl;
      }
      return baseUrl.replace("<type>", this.smartStructureCategory);
    }
  },
  mounted() {
    if (
      this.$router.currentRoute.matched.find(
        match => match.name === this.routeName
      )
    ) {
      this.selectedStructure =
        this.$router.currentRoute.params[this.routeParamField] || null;
      this.$once("smart-structure-list-ready", () => {
        this.selectStructure(this.selectedStructure);
      });
    }
    if (this.$router.currentRoute.query.filter) {
      this.listFilter = this.$router.currentRoute.query.filter;
    }
  },
  methods: {
    readData(options) {
      this.$http
        .get(this.resolveListUrl, {
          params: options.data,
          paramsSerializer: kendo.jQuery.param
        })
        .then(response => {
          options.success(response);
        })
        .catch(error => {
          options.error(error);
        });
    },
    parseData(response) {
      if (response && response.data && response.data.data) {
        return response.data.data;
      }
      return [];
    },
    selectStructure(structureName) {
      this.$nextTick(() => {
        const domItem = this.$refs.listview
          .kendoWidget()
          .element.children(
            `.smart-structure-list-item[data-smartstructname=${structureName}]`
          );
        this.$refs.listview.kendoWidget().select(domItem);
      });
    },
    structureItemTemplate(e) {
      return {
        template: Vue.component(structureItemVue.name, structureItemVue),
        templateArgs: {
          to: {
            name: this.routeName,
            params: { [this.routeParamField]: e.name }
          },
          structure: e,
          parentComponent: this.$refs.listview,
          parentVueComponent: this
        }
      };
    },
    clearFilter() {
      this.listFilter = "";
    },
    onDataBound() {
      this.$emit("smart-structure-list-ready");
    }
  }
};
