import Vue from "vue";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";

Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);
Vue.use(ButtonsInstaller);

export default {
  components: {
    Grid
  },
  data() {
    return {
      enumDataSource: ""
    };
  },
  mounted() {
    $(window).resize(() => {
      this.$refs.enumGridContent.kendoWidget().resize();
    });
  },
  methods: {
    getEnum(options) {
      this.$http
        .get("/api/v2/devel/smart/enumerates/", {
          params: options.data,
          paramsSerializer: kendo.jQuery.param
        })
        .then(response => {
          options.success(response);
        })
        .catch(response => {
          options.error(response);
        });
    },
    parseEnumData(response) {
      if (response && response.data && response.data.data) {
        return response.data.data.enumerates;
      }
      return [];
    },
    parseEnumTotal(response) {
      return response.data.data.requestParameters.total;
    },
    refreshEnum() {
      this.$refs.enumGridContent.kendoWidget().dataSource.filter({});
      this.$refs.enumGridContent.kendoWidget().dataSource.read();
    },
    nameFilter(args) {
      args.element.kendoDropDownList({
        dataTextField: "text",
        dataValueField: "value",
        valuePrimitive: true,
        dataSource: new kendo.data.DataSource({
          transport: {
            read: {
              url: "/api/v2/devel/smart/enumerates/?take=all&skip=0"
            }
          },
          schema: {
            data: response => {
              let results = [];
              response.data.enumerates.forEach(item => {
                results.push({
                  text: item.name,
                  value: item.name
                });
              });
              return results.filter(
                (el, i, a) => a.findIndex(el2 => el2.text === el.text) === i
              );
            }
          }
        })
      });
    },
    disabledFilter(args) {
      args.element.kendoDropDownList({
        valuePrimitive: true,
        dataSource: [true.toString(), false.toString()]
      });
    }
  }
};
