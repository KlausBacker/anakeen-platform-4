import Vue from "vue";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";

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
      dataSource: null,
      listModel: {
        id: "name"
      },
      listFilter: ""
    };
  },
  watch: {
    listFilter(newValue, oldValue) {
      if (newValue !== oldValue) {
        if (this.dataSource) {
          if (this.$router.currentRoute.query.filter !== newValue) {
            this.$router.push({ query: { filter: newValue } });
          }
          this.dataSource.filter({
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
    translations() {
      return {
        listFilterPlaceholder: "Search a structure"
      };
    },
    tabs() {
      if (this.dataSource) {
        const view = this.dataSource.view();
        if (view.length) {
          return view.toJSON();
        }
      }
      return [];
    },
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
    this.dataSource = this.$refs.remoteDataSource.kendoWidget();
    this.dataSource.read();
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
    clearFilter() {
      this.listFilter = "";
    }
  }
};
