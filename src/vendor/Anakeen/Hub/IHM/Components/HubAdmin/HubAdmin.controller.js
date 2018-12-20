import Vue from "vue";
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
  mounted() {
    Object.keys(this.childFam).forEach(key => {
      const elt = this.childFam[key];
      this.hubConfig.push({ text: elt.title, value: elt.name });
    });
  },
  methods: {
    toolbarConfig() {
      const options = this.$refs.hubGrid.kendoGrid.getOptions();
      options.toolbar.push({
        name: "Create",
        template: "<select class='hub-config-list'/>"
      });
      this.$refs.hubGrid.kendoGrid.setOptions(options);
      this.$(".hub-config-list").kendoDropDownList({
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
    },
    actionClick(e) {
      e.preventDefault();
      this.$refs.hubAdminSplitter.disableEmptyContent();
      this.$nextTick(() => {
        if (this.$refs.smartConfig && this.$refs.smartConfig.isLoaded()) {
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
