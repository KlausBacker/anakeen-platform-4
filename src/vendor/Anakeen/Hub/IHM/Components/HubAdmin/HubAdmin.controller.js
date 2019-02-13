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
  name: "ank-hub-admin",
  components: {
    grid: AnkSEGrid,
    identity: AnkIdentity,
    logout: AnkLogout,
    smartElem: AnkSmartElement,
    "ank-splitter": Splitter
  },
  data() {
    return {
      // eslint-disable-next-line no-undef
      childFam: window.ankChildFam,
      collection: "",
      hubId: "",
      hubTitle: "",
      hubIcon: "",
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
  created() {
    let route = window.location.href;
    this.hubId = route.match(/\/hub\/admin\/(\w+)/)[1];
    this.$http
      .get(`/api/v2/smart-elements/${this.hubId}.json`)
      .then(response => {
        this.hubTitle =
          response.data.data.document.attributes.hub_instance_title[0].displayValue;
      });
  },
  mounted() {
    Object.keys(this.childFam).forEach(key => {
      const elt = this.childFam[key];
      this.hubConfig.push({ text: elt.title, value: elt.name });
    });
  },
  methods: {
    toolbarConfig() {
      this.$(".grid-toolbar-create-action").kendoDropDownList({
        dataTextField: "text",
        dataValueField: "value",
        dataSource: this.hubConfig,
        valueTemplate: "Create",
        select: e => {
          this.selectConfig(e);
        }
      });
    },
    selectConfig(e) {
      this.collection = e.dataItem.value;
      this.$refs.hubAdminSplitter.disableEmptyContent();
      this.$nextTick(() => {
        if (this.$refs.smartConfig.isLoaded()) {
          this.createConfig(this.collection);
        } else {
          this.$refs.smartConfig.$once("documentLoaded", () => {
            this.createConfig(this.collection);
          });
        }
      });
    },
    createConfig(e) {
      this.$refs.hubAdminSplitter.disableEmptyContent();
      this.$refs.smartConfig.fetchSmartElement({
        initid: e,
        viewId: "!defaultCreation"
      });
      this.$refs.smartConfig.addEventListener("ready", () => {
        this.$refs.smartConfig.addCustomClientData({ hubId: this.hubId });
      });
    },
    openConfig(e) {
      this.$refs.smartConfig.fetchSmartElement({
        initid: e,
        viewId: "!defaultConsultation"
      });
    },
    modifyConfig(e) {
      this.$refs.smartConfig.fetchSmartElement({
        initid: e,
        viewId: "!defaultEdition"
      });
      this.$refs.smartConfig.addEventListener("ready", () => {
        this.$refs.smartConfig.addCustomClientData({ hubId: this.hubId });
      });
    },
    toolbarActionClick(e) {
      switch (e.data.type) {
        case "consult":
          window.open(`/hub/station/${this.hubId}/`);
          break;
      }
    },
    actionClick(e) {
      e.preventDefault();
      this.$refs.hubAdminSplitter.disableEmptyContent();
      this.$nextTick(() => {
        if (this.$refs.smartConfig && this.$refs.smartConfig.isLoaded()) {
          this.$refs.smartConfig.addEventListener("afterSave", () => {
            if (this.$refs.hubGrid && this.$refs.hubGrid.dataSource) {
              this.$refs.hubGrid.kendoGrid.dataSource.read();
            }
          });
          this.$refs.smartConfig.addEventListener("afterDelete", () => {
            if (this.$refs.hubGrid && this.$refs.hubGrid.dataSource) {
              this.$refs.hubGrid.kendoGrid.dataSource.read();
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
          this.$refs.smartConfig.$once("documentLoaded", () => {
            this.$refs.smartConfig.addEventListener("afterSave", () => {
              if (this.$refs.hubGrid && this.$refs.hubGrid.dataSource) {
                this.$refs.hubGrid.kendoGrid.dataSource.read();
              }
            });
            this.$refs.smartConfig.addEventListener("afterDelete", () => {
              if (this.$refs.hubGrid && this.$refs.hubGrid.dataSource) {
                this.$refs.hubGrid.kendoGrid.dataSource.read();
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
};
