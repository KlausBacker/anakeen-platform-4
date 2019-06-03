import Vue from "vue";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";

Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);
Vue.use(Grid);

const getIsoAlpha2Code = lang => {
  switch (lang) {
    case "en":
      return "us";
    default:
      return lang;
  }
};

export default {
  components: {
    Grid
  },
  props: {
    localizationUrlContent: {
      type: String,
      default: "/api/v2/devel/i18n/"
    }
  },
  data() {
    return {
      listModel: {
        id: "ankId"
      },
      supportedLanguages: [
        {
          title: "English",
          field: "en"
        },
        {
          title: "French",
          field: "fr"
        }
      ]
    };
  },
  devCenterRefreshData() {
    if (this.$refs.dataSource) {
      this.$refs.dataSource.kendoWidget().read();
    }
  },
  mounted() {
    this.$(window).on("resize", () => {
      const kendoGrid = this.$refs.localizationGrid
        ? this.$refs.localizationGrid.kendoWidget()
        : null;
      if (kendoGrid) {
        kendoGrid.resize();
      }
    });
  },
  beforeCreate() {
    this.privateMethods = {
      readData: options => {
        this.$http
          .get(this.localizationUrlContent)
          .then(response => {
            options.success(response);
          })
          .catch(err => {
            console.error(err);
            options.success({ data: { data: [] } });
            // throw err;
          });
      },
      parseData: response => {
        const data = response.data.data;
        return data.map(datum => {
          if (datum) {
            const result = Object.assign({}, datum);
            result.ankId = `${result.msgctxt}::${result.msgid}`;
            return result;
          }
          return datum;
        });
      },
      countryHeaderTemplate: lang => {
        return `
          <span    class="country-header">
              <span class="country-label">${lang.title}</span>
          </span>
        `;
      },
      filterTemplate: colId => args => {
        args.element
          .parent()
          .html(
            `<input data-bind="value: value" data-field="${colId}" class="k-textbox filter-input">`
          );
      },
      filesTemplate: () => args => {
        let cellData = "";
        if (args.files) {
          let eFiles = args.files.map(file => kendo.htmlEncode(file));
          cellData = "<ul><li>";
          cellData += eFiles.join("</li><li>");
          cellData += "</li></ul>";
          return cellData;
        }
        return cellData;
      }
    };
  }
};
