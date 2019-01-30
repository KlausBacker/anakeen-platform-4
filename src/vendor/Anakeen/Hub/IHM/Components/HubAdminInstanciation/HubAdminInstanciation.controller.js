import Vue from "vue";
import "@progress/kendo-ui/js/kendo.popup";
import "@progress/kendo-ui/js/kendo.grid";

import { AnkSEGrid } from "@anakeen/ank-components";
import { AnkLogout } from "@anakeen/ank-components";
import { AnkIdentity } from "@anakeen/ank-components";
import { AnkSmartElement } from "@anakeen/ank-components";
import Splitter from "../Splitter/Splitter.vue";

Vue.use(Splitter);

export default {
  name: "ank-hub-instanciation",
  components: {
    grid: AnkSEGrid,
    identity: AnkIdentity,
    logout: AnkLogout,
    smartElem: AnkSmartElement,
    "ank-splitter": Splitter
  },
  data() {
    return {
      collection: "",
      hubConfig: [],
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
        },
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
        }
      ]
    };
  },
  methods: {
    createHubStation() {
      this.$refs.instanceConfig.fetchSmartElement({
        initid: "HUBINSTANCIATION",
        viewId: "!defaultCreation"
      });
    },
    openConfig(e) {
      this.$refs.instanceConfig.fetchSmartElement({
        initid: e,
        viewId: "!defaultConsultation"
      });
    },
    modifyConfig(e) {
      this.$refs.instanceConfig.fetchSmartElement({
        initid: e,
        viewId: "!defaultEdition"
      });
    },
    configureStation: function(e) {
      let route = this.$router.resolve({
        path: `/hub/admin/${e}`
      });
      window.open(route.href);
    },
    toolbarActionClick(e) {
      switch (e.data.type) {
        case "create":
          this.$refs.hubInstanciationSplitter.disableEmptyContent();
          this.$nextTick(() => {
            if (this.$refs.instanceConfig.isLoaded()) {
              this.createHubStation();
            } else {
              this.$refs.instanceConfig.$once("documentLoaded", () => {
                this.createHubStation();
              });
            }
          });
          break;
        default:
          break;
      }
    },
    actionClick(e) {
      e.preventDefault();
      if (e.data.type === "configure") {
        this.configureStation(e.data.row.id);
      } else {
        this.$refs.hubInstanciationSplitter.disableEmptyContent();
        this.$nextTick(() => {
          if (
            this.$refs.instanceConfig &&
            this.$refs.instanceConfig.isLoaded()
          ) {
            this.$refs.instanceConfig.addEventListener("afterSave", () => {
              if (
                this.$refs.hubInstanciationGrid &&
                this.$refs.hubInstanciationGrid.dataSource
              ) {
                this.$refs.hubInstanciationGrid.kendoGrid.dataSource.read();
              }
            });
            this.$refs.instanceConfig.addEventListener("afterDelete", () => {
              if (
                this.$refs.hubInstanciationGrid &&
                this.$refs.hubInstanciationGrid.dataSource
              ) {
                this.$refs.hubInstanciationGrid.kendoGrid.dataSource.read();
              }
            });
            switch (e.data.type) {
              case "consult":
                this.openConfig(e.data.row.id);
                break;
              case "edit":
                this.modifyConfig(e.data.row.id);
                break;
              default:
                break;
            }
          } else {
            this.$refs.instanceConfig.$once("documentLoaded", () => {
              this.$refs.instanceConfig.addEventListener("afterSave", () => {
                if (
                  this.$refs.hubInstanciationGrid &&
                  this.$refs.hubInstanciationGrid.dataSource
                ) {
                  this.$refs.hubInstanciationGrid.kendoGrid.dataSource.read();
                }
              });
              this.$refs.instanceConfig.addEventListener("afterDelete", () => {
                if (
                  this.$refs.hubInstanciationGrid &&
                  this.$refs.hubInstanciationGrid.dataSource
                ) {
                  this.$refs.hubInstanciationGrid.kendoGrid.dataSource.read();
                }
              });
              switch (e.data.type) {
                case "consult":
                  this.openConfig(e.data.row.id);
                  break;
                case "edit":
                  this.modifyConfig(e.data.row.id);
                  break;
                default:
                  break;
              }
            });
          }
        });
      }
    }
  }
};
