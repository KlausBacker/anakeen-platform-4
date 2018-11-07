import Vue from "vue";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { AnkSmartElement } from "@anakeen/ank-components";

Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);
Vue.use(ButtonsInstaller);
Vue.use(AnkSmartElement);

export default {
  components: {
    Grid,
    "ank-smart-element": AnkSmartElement
  },
  data() {
    return {
      accessDataSource: ""
    };
  },
  mounted() {
    $(window).resize(() => {
      if (this.$refs.accessGridContent) {
        this.$refs.accessGridContent.kendoWidget().resize();
      }
    });
  },
  methods: {
    getAccessConf() {
      return [];
    },
    parseAccessConfData(response) {
      return response;
    },
    parseAccessConfTotal(response) {
      return response;
    },
    refreshAccessConf() {
      this.$refs.accessGridContent.kendoWidget().dataSource.filter({});
      this.$refs.accessGridContent.kendoWidget().dataSource.read();
    },
    autoFilterCol(e) {
      e.element.addClass("k-textbox filter-input");
    },
    displayConsult() {
      return "<button class='openConsult'></button>";
    }
  }
};
