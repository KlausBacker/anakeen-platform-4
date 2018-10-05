import Vue from "vue";
import { AnkSEGrid } from "@anakeen/ank-components";
import { AnkLogout } from "@anakeen/ank-components";
import { AnkIdentity } from "@anakeen/ank-components";
import { AnkSmartElement } from "@anakeen/ank-components";
import VModal from "vue-js-modal";

const grid = AnkSEGrid;
const identity = AnkIdentity;
const logout = AnkLogout;
const smartElem = AnkSmartElement;

Vue.use(VModal);

export default {
  name: "ank-hub-admin",
  components: {
    grid,
    identity,
    logout,
    smartElem
  },
  data() {
    return {
      collection: "",
      hubConfig: [
        { text: "HUBCONFIGURATION", value: "1" },
        { text: "HUBCONFIGURATIONIDENTITY", value: "2" },
        { text: "HUBCONFIGURATIONLOGOUT", value: "3" },
        { text: "HUBCONFIGURATIONSLOT", value: "4" }
      ]
    };
  },
  methods: {
    toolbarConfig() {
      const options = this.$refs.hubGrid.kendoGrid.getOptions();
      options.toolbar.push({
        name: "Create",
        template: "<select class='hub-config-list' style='width:20%;'/>"
      });
      this.$refs.hubGrid.kendoGrid.setOptions(options);
      this.$(".hub-config-list").kendoDropDownList({
        dataTextField: "text",
        dataValueField: "value",
        dataSource: this.hubConfig,
        optionLabel: "Create",
        select: e => {
          this.selectConfig(e);
        }
      });
    },
    selectConfig(e) {
      this.$modal.show("hubConfigModal");
      this.collection = e.item.text();
    },
    openedModal() {
      if (this.$refs.smartConfig.isLoaded()) {
        this.createConfig(this.collection);
      } else {
        this.$refs.smartConfig.$once("documentLoaded", () => {
          this.createConfig(this.collection);
        });
      }
    },
    createConfig(e) {
      this.$refs.smartConfig.fetchSmartElement({
        initid: e,
        viewId: "!defaultCreation"
      });
      this.$refs.smartConfig.addEventListener("beforeSave", () => {
        this.$modal.hide("hubConfigModal");
      });
    }
  }
};
