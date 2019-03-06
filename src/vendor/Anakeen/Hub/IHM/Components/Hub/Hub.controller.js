import { HubStation } from "@anakeen/hub-components";
import HubEntries from "./utils/hubEntry";

export default {
  name: "ank-hub",
  components: {
    HubStation
  },
  data() {
    return {
      config: {},
      hubId: ""
    };
  },
  created() {
    this.hubEntries = new HubEntries(this);
    this.hubId = window.AnkHubInstanceId;
  },
  mounted() {
    this.getConfig();
  },
  methods: {
    getConfig() {
      this.$kendo.ui.progress(this.$(this.$el), true);
      this.$http
        .get(`/hub/config/${this.hubId}`)
        .then(response => {
          const data = response.data.data;
          const globalAssets = data.globalAssets || [];
          this.hubEntries.contents = [{ assets: globalAssets }].concat(
            data.hubElements
          );
          this.hubEntries.loadAssets().then(() => {
            this.$kendo.ui.progress(this.$(this.$el), false);
            this.hubEntries.useComponents();
            this.config = data;
          });
        })
        .catch(error => {
          console.error(error);
          this.$kendo.ui.progress(this.$(this.$el), false);
        });
    }
  }
};
