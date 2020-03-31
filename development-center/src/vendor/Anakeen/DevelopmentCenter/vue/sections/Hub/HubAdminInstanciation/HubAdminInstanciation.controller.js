import "@progress/kendo-ui/js/kendo.popup";
import "@progress/kendo-ui/js/kendo.grid";
import { Vue } from "vue-property-decorator";

import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import AnkSEList from "@anakeen/user-interfaces/components/lib/AnkSmartElementList.esm";
import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import AnkHubAdmin from "../HubAdmin/HubAdmin.vue";

Vue.use(ButtonsInstaller);

export default {
  name: "ank-hub-instanciation",
  components: {
    "ank-smart-element": () => AnkSmartElement,
    "ank-se-list": AnkSEList,
    "ank-splitter": AnkSplitter,
    "ank-hub-admin": AnkHubAdmin
  },
  props: ["hubInstanceSelected", "hubComponentSelected"],
  watch: {
    selectedHub(newValue) {
      this.$emit("hubInstanceSelected", newValue);
    },
    selectedComponent(value) {
      this.$emit("hubComponentSelected", value);
    }
  },
  data() {
    return {
      selectedComponent: this.hubComponentSelected,
      collection: "",
      hubConfig: [],
      selectedHub: this.hubInstanceSelected,
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
      let elementID = e.data.properties.initid;
      this.selectedHub = elementID;
      this.displayConfig = true;
      this.$nextTick(() => {
        this.listenElement();
      });
      this.$refs.hubInstanciationSplitter.disableEmptyContent();
    },
    listenElement() {
      if (this.$refs.ankHubAdmin) {
        const hAdmin = this.$refs.ankHubAdmin;
        if (hAdmin.$refs.smartConfig) {
          hAdmin.openElement();
          const element = this.$refs.ankHubAdmin.$refs.smartConfig;
          if (element.isLoaded()) {
            element.addEventListener("afterSave", () => {
              this.$refs.hubInstanciationList.refreshList();
            });
            element.addEventListener("afterDelete", () => {
              this.$refs.hubInstanciationList.refreshList();
            });
          } else {
            element.$on("smartElementMounted", () => {
              element.addEventListener("afterSave", () => {
                this.$refs.hubInstanciationList.refreshList();
              });
              element.addEventListener("afterDelete", () => {
                this.$refs.hubInstanciationList.refreshList();
              });
            });
          }
        }
      }
    },
    openElementInfo({ initid, viewId = "!defaultConsultation" }) {
      this.$refs.hubInstanciationSplitter.disableEmptyContent();

      this.$nextTick(() => {
        this.$refs.instanceConfig.fetchSmartElement({
          initid: initid,
          viewId: viewId
        });
        if (this.$refs.instanceConfig.isLoaded()) {
          this.$refs.instanceConfig.addEventListener("afterSave", (e, doc) => {
            if (this.$refs.hubInstanciationList) {
              this.$refs.hubInstanciationList.refreshList();
              this.displayConfig = true;
              this.selectedHub = doc.id;
              if (this.$refs.ankHubAdmin) {
                const hAdmin = this.$refs.ankHubAdmin;
                if (hAdmin.$refs.smartConfig) {
                  const element = hAdmin.$refs.smartConfig;
                  if (element.isLoaded()) {
                    element.addEventListener("afterDelete", () => {
                      this.$refs.hubInstanciationList.refreshList();
                    });
                  } else {
                    element.$once("smartElementMounted", () => {
                      this.$refs.hubInstanciationList.refreshList();
                    });
                  }
                }
              }
            }
          });
        } else {
          this.$refs.instanceConfig.$once("smartElementMounted", () => {
            this.$refs.instanceConfig.addEventListener("afterSave", (e, doc) => {
              if (this.$refs.hubInstanciationList) {
                this.$refs.hubInstanciationList.refreshList();
                this.displayConfig = true;
                this.selectedHub = doc.id;
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
            this.createHubStation();
          });
          break;
        default:
          break;
      }
    },
    onListDataBound() {
      if (this.hubInstanceSelected) {
        this.$nextTick(() => {
          this.$refs.hubInstanciationList.selectSmartElement(this.hubInstanceSelected);
        });
      }
    },
    onHubComponentSelected(hubComponent) {
      this.selectedComponent = hubComponent;
    },
    onListClicked() {
      // reset componet selection
      this.selectedComponent = 0;
    }
  }
};
