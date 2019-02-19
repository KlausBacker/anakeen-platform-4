import { HubStation } from "@anakeen/hub-components";
import HubEntries from "./utils/hubEntry";

export default {
  name: "ank-hub",
  components: {
    HubStation
  },
  data() {
    return {
      config: [],
      hubId: ""
    };
  },
  created() {
    this.hubEntries = new HubEntries(this);
    let route = window.location.href;
    this.hubId = route.match(/\/hub\/station\/(\w+)/)[1];
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
          this.hubEntries.contents = data;
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
