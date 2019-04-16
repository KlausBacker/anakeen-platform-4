import "@progress/kendo-ui/js/kendo.popup";
import "@progress/kendo-ui/js/kendo.grid";
import Vue from "vue";

import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import AnkSEList from "@anakeen/user-interfaces/components/lib/AnkSEList";
import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement";
import AnkHubAdmin from "../HubAdmin/HubAdmin.vue";

Vue.use(ButtonsInstaller);

export default {
  name: "ank-hub-instanciation",
  components: {
    "ank-smart-element": AnkSmartElement,
    "ank-se-list": AnkSEList,
    "ank-splitter": AnkSplitter,
    "ank-hub-admin": AnkHubAdmin
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
      this.$nextTick(() => {
        const element = this.$refs.ankHubAdmin.$refs.smartConfig;
        if (element) {
          if (element.isLoaded()) {
            element.addEventListener("afterSave", () => {
              this.$refs.hubInstanciationList.refreshList();
            });
            element.addEventListener("afterDelete", () => {
              this.$refs.hubInstanciationList.refreshList();
            });
          } else {
            element.$on("documentLoaded", () => {
              element.addEventListener("afterSave", () => {
                this.$refs.hubInstanciationList.refreshList();
              });
              element.addEventListener("afterDelete", () => {
                this.$refs.hubInstanciationList.refreshList();
              });
            });
          }
        }
      });
      this.$refs.hubInstanciationSplitter.disableEmptyContent();
    },

    openElementInfo({ initid, viewId = "!defaultConsultation" }) {
      this.$refs.hubInstanciationSplitter.disableEmptyContent();

      this.$nextTick(() => {
        if (this.$refs.instanceConfig.isLoaded()) {
          this.$refs.instanceConfig.addEventListener("afterSave", () => {
            if (this.$refs.hubInstanciationList) {
              this.$refs.hubInstanciationList.refreshList();
            }
          });
          this.$refs.instanceConfig.addEventListener("afterDelete", () => {
            if (this.$refs.hubInstanciationList) {
              this.$refs.hubInstanciationList.refreshList();
            }
          });
          this.$refs.instanceConfig.fetchSmartElement({
            initid: initid,
            viewId: viewId
          });
        } else {
          this.$refs.instanceConfig.$once("documentLoaded", () => {
            this.$refs.instanceConfig.addEventListener("afterSave", () => {
              if (this.$refs.hubInstanciationList) {
                this.$refs.hubInstanciationList.refreshList();
              }
            });
            this.$refs.instanceConfig.addEventListener("afterDelete", () => {
              if (this.$refs.hubInstanciationList) {
                this.$refs.hubInstanciationList.refreshList();
              }
            });
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
    }
    // },
    // actionClick(e) {
    //   e.preventDefault();
    //   if (e.data.type === "configure") {
    //     this.configureStation(e.data.row.id);
    //   } else {
    //     this.$refs.hubInstanciationSplitter.disableEmptyContent();
    //     this.$nextTick(() => {
    //       console.log("coucou");
    //       if (this.$refs.instanceConfig) {
    //         console.log("ofejojofeojg");
    //         if (this.$refs.instanceConfig.isLoaded()) {
    //           this.$refs.instanceConfig.addEventListener("afterSave", () => {
    //             console.log("coucou");
    //             if (this.$refs.hubInstanciationList) {
    //               this.$refs.hubInstanciationList.refreshList();
    //             }
    //           });
    //           this.$refs.instanceConfig.addEventListener("afterDelete", () => {
    //             if (this.$refs.hubInstanciationList) {
    //               this.$refs.hubInstanciationList.refreshList();
    //             }
    //           });
    //           switch (e.data.type) {
    //             case "consult":
    //               this.openConfig(e.data.row.id);
    //               break;
    //             case "edit":
    //               this.modifyConfig(e.data.row.id);
    //               break;
    //             default:
    //               break;
    //           }
    //         } else {
    //           this.$refs.instanceConfig.$once("documentLoaded", () => {
    //             this.$refs.instanceConfig.addEventListener("afterSave", () => {
    //               console.log("coucou");
    //               if (this.$refs.hubInstanciationList) {
    //                 this.$refs.hubInstanciationList.refreshList();
    //               }
    //             });
    //             this.$refs.instanceConfig.addEventListener(
    //               "afterDelete",
    //               () => {
    //                 if (this.$refs.hubInstanciationList) {
    //                   this.$refs.hubInstanciationList.refreshList();
    //                 }
    //               }
    //             );
    //             switch (e.data.type) {
    //               case "consult":
    //                 this.openConfig(e.data.row.id);
    //                 break;
    //               case "edit":
    //                 this.modifyConfig(e.data.row.id);
    //                 break;
    //               default:
    //                 break;
    //             }
    //           });
    //         }
    //       }
    //     });
    //   }
  }
};
