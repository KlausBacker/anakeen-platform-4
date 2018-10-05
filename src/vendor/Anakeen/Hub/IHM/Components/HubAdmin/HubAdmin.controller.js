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
      // eslint-disable-next-line no-undef
      childFam: window.ankChildFam,
      collection: "",
      hubConfig: []
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
        template: "<select class='hub-config-list' style='width:20%;'/>"
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
      this.$modal.show("hubConfigModal");
      this.collection = e.sender.value();
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
