import "@progress/kendo-ui/js/kendo.popup";
import "@progress/kendo-ui/js/kendo.grid";
import Vue from "vue";

import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import AnkSEList from "@anakeen/user-interfaces/components/lib/AnkSEList";
import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement";

Vue.use(ButtonsInstaller);

export default {
  name: "ank-hub-instanciation",
  components: {
    "ank-smart-element": AnkSmartElement,
    "ank-se-list": AnkSEList,
    "ank-splitter": AnkSplitter
  },
  data() {
    return {
      collection: "",
      hubConfig: [],
      selectedHub: 0,
      displayConfig: false,
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "250px"
        },
        {
          scrollable: false,
          collapsible: true,
          resizable: true
        }
      ]
    };
  },
  methods: {
    createHubStation() {
      this.displayConfig = false;

      this.openElementInfo({
        initid: "HUBINSTANCIATION",
        viewId: "!defaultCreation"
      });
    },
    openConfig(e) {
      let elementID = e.detail[0].initid;
      this.selectedHub = elementID;
      this.displayConfig = true;

      this.$refs.hubInstanciationSplitter.disableEmptyContent();
    },

    openElementInfo({ initid, viewId = "!defaultConsultation" }) {
      this.$refs.hubInstanciationSplitter.disableEmptyContent();

      this.$nextTick(() => {
        if (this.$refs.instanceConfig.isLoaded()) {
          this.$refs.instanceConfig.fetchSmartElement({
            initid: initid,
            viewId: viewId
          });
        } else {
          this.$refs.instanceConfig.$once("documentLoaded", () => {
            this.$refs.instanceConfig.fetchSmartElement({
              initid: initid,
              viewId: viewId
            });
          });
        }
      });
    },
    configureStation: function(e) {
      window.open(`/hub/admin/${e}`);
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
