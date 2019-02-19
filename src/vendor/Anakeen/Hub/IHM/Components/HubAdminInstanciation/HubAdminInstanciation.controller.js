import "@progress/kendo-ui/js/kendo.popup";
import "@progress/kendo-ui/js/kendo.grid";

import { AnkSEGrid } from "@anakeen/user-interfaces";
import { AnkLogout } from "@anakeen/user-interfaces";
import { AnkIdentity } from "@anakeen/user-interfaces";
import { AnkSmartElement } from "@anakeen/user-interfaces";
import { AnkSplitter } from "@anakeen/internal-components";

export default {
  name: "ank-hub-instanciation",
  components: {
    grid: AnkSEGrid,
    identity: AnkIdentity,
    logout: AnkLogout,
    smartElem: AnkSmartElement,
    "ank-splitter": AnkSplitter
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
    cellRender(event) {
      if (event.data && event.data.columnConfig) {
        switch (event.data.columnConfig.field) {
          case "icon":
            event.data.cellRender.html(
              `<img src=${
                event.data.cellData
              } alt="instanceIcon" width="16" height="16"/>`
            );
        }
      }
    },
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
