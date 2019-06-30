import Vue from "vue";
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
  props: ["wflName"],
  data() {
    return {
      stepsDataSource: "",
      ssName: ""
    };
  },
  devCenterRefreshData() {
    this.refreshSteps();
  },
  methods: {
    getSteps(options) {
      this.$http
        .get(`/api/v2/devel/smart/workflows/${this.wflName}`, {
          params: options.data,
          paramsSerializer: kendo.jQuery.param
        })
        .then(response => {
          options.success(response);
        })
        .catch(response => {
          options.error(response);
        });
      return [];
    },
    parseStepsData(response) {
      if (response && response.data && response.data.data) {
        this.ssName = response.data.data.properties.structure;
        return response.data.data.steps;
      }
      return [];
    },
    refreshSteps() {
      this.$refs.stepsGridContent.kendoWidget().dataSource.read();
    },
    autoFilterCol(e) {
      e.element.addClass("k-textbox filter-input");
    },
    displayMultiple(item) {
      if (item instanceof Object) {
        let str = "";
        return this.recursiveData(item, str);
      }
      return item;
    },
    recursiveData(items, str) {
      if (items instanceof Object) {
        Object.keys(items.toJSON()).forEach(item => {
          if (items[item] instanceof Object) {
            this.recursiveData(items[item], str);
          } else {
            str += `<li><a data-role="develRouterLink" href="/devel/smartElements/${items[item]}/view/?name=${items[item]}">${items[item]}</a></li>`;
          }
        });
      }
      return str;
    },
    displayLink(colId) {
      return dataItem => {
        switch (colId) {
          case "UI":
            return `<ul><li><b>View control :&nbsp</b><a data-role="develRouterLink" href="/devel/ui/${
              this.ssName
            }/views/?cvId=${this.checkIsValid(
              dataItem["viewcontrol"]
            )}">${this.checkIsValid(
              dataItem["viewcontrol"]
            )}</a></li><li><b>Mask :&nbsp</b><a data-role="develRouterLink" href="/devel/ui/${
              this.ssName
            }/views/?maskId=${this.checkIsValid(
              dataItem["mask"]
            )})}">${this.checkIsValid(dataItem["mask"])}</a></li></ul>`;
          case "security":
            return `<ul><li><b>Profil&nbsp:&nbsp</b><a data-role="develRouterLink" href="/devel/security/profiles/${this.checkIsValid(
              dataItem["profil"]
            )}/?name=${this.checkIsValid(
              dataItem["profil"]
            )}">${this.checkIsValid(
              dataItem["profil"]
            )}</a></li><li><b>Smart field access&nbsp:&nbsp</b><a data-role="develRouterLink" href="/devel/security/fieldAccess/${this.checkIsValid(
              dataItem["fall"]
            )}/config">${this.checkIsValid(dataItem["fall"])}</a></li></ul>`;
          case "settings":
            return `<div><b>Mail Templates&nbsp:&nbsp</b>${this.displayMultiple(
              dataItem["mailtemplates"]
            )}</div><ul><li><b>Timer&nbsp:&nbsp</b><a data-role="develRouterLink" href="/devel/smartElements/${this.checkIsValid(
              dataItem["timer"]
            )}/view/?name=${this.checkIsValid(
              dataItem["timer"]
            )}">${this.checkIsValid(dataItem["timer"])}</a></li></ul></div>`;
          default:
            return this.checkIsValid(dataItem[colId]);
        }
      };
    },
    displayData(colId) {
      return dataItem => {
        let str = "";
        switch (colId) {
          case "step":
            str = `<ul><li><b>Name :&nbsp</b>${this.checkIsValid(
              dataItem["id"]
            )}</li><li><b>Label :&nbsp</b>${this.checkIsValid(
              dataItem["label"]
            )}</li><li><b>Color :&nbsp</b><div class='chip-color' style='background-color:${this.checkIsValid(
              dataItem["color"]
            )}'></div><span>&nbsp${this.checkIsValid(
              dataItem["color"]
            )}</span></li><li><b>Activity :&nbsp</b>${this.checkIsValid(
              dataItem["activity"]
            )}</li></ul>`;
            return str;
        }
      };
    },
    checkIsValid(item) {
      if (item === null || item === undefined) {
        return "";
      } else {
        return item;
      }
    }
  }
};
