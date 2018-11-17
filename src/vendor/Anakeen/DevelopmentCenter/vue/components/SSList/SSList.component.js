import Vue from "vue";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";

import { vendorCategory } from "../../store/getters";

Vue.use(DataSourceInstaller);

export default {
  name: "ss-list",
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
    vendorCategory: {
      type: String,
      default: "auto",
      validator: value => {
        const validValues = ["auto", "all", "vendor"];
        const valid = validValues.indexOf(value) >= 0;
        if (!valid) {
          console.error(
            `Property "vendorCategory" must be in ${JSON.stringify(
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
        id: "id"
      },
      listFilter: ""
    };
  },
  watch: {
    listFilter(newValue, oldValue) {
      if (newValue !== oldValue) {
        this.filterList(newValue);
      }
    },
    vendorCategory() {
      if (this.dataSource) {
        this.dataSource.read();
      }
    }
  },
  computed: {
    translations() {
      return {
        listFilterPlaceholder: "Search a structure"
      };
    },
    vendorType() {
      let value = this.vendorCategory;
      if (value === "auto") {
        value = this.$store.getters.vendorCategory;
      }
      return value;
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
    filterPlaceholder() {
      if (this.hasFilter && typeof this.filter === "object") {
        return (
          this.filter.placeholder || this.translations.listFilterPlaceholder
        );
      }
      return this.translations.listFilterPlaceholder;
    },
    resolveListUrl() {
      const baseUrl = this.listUrl;
      if (baseUrl.indexOf("<type>") < -1) {
        return baseUrl;
      }
      return baseUrl.replace("<type>", this.vendorType);
    }
  },
  mounted() {
    this.dataSource = this.$refs.remoteDataSource.kendoWidget();
    if (this.$router.currentRoute.query.filter) {
      this.listFilter = this.$router.currentRoute.query.filter;
    }
    this.dataSource.read();
    this.$store.watch(vendorCategory, () => {
      if (this.vendorCategory === "auto") {
        if (this.dataSource) {
          this.dataSource.read();
        }
      }
    });
  },
  methods: {
    filterList(filterValue) {
      if (this.dataSource) {
        if (this.$router.currentRoute.query.filter !== filterValue) {
          this.$router.addQueryParams({ filter: filterValue });
        }
        let filterObject = {
          logic: "or",
          filters: [
            {
              field: "name",
              operator: "contains",
              value: filterValue
            }
          ]
        };
        if (this.hasFilter && typeof this.filter === "object") {
          if (typeof this.filter.doFilter === "function") {
            filterObject = this.filter.doFilter.call(
              null,
              filterValue,
              filterObject
            );
          }
        }
        this.dataSource.filter(filterObject);
      }
    },
    readData(options) {
      this.$http
        .get(this.resolveListUrl)
        .then(response => {
          options.success(response);
          this.$nextTick(() => {
            const active = this.$(".router-link-active", this.$el);
            if (active.length) {
              active[0].scrollIntoView();
            }
          });
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
