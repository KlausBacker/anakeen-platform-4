import Vue from "vue";
import { AnkDock } from "@anakeen/ank-components";
import { AnkLogout } from "@anakeen/ank-components";
import { AnkIdentity } from "@anakeen/ank-components";

import HubEntries from "./utils/hubEntry";

Vue.use(AnkLogout, {
  globalVueComponent: true
});

Vue.use(AnkIdentity, {
  globalVueComponent: true
});

export default {
  name: "ank-hub",
  components: {
    AnkDock,
    AnkLogout,
    AnkIdentity
  },
  data() {
    return {
      config: [],
      content: {
        top: [],
        bottom: [],
        left: [],
        right: []
      }
    };
  },
  created() {
    this.hubEntries = new HubEntries(this);
  },
  mounted() {
    this.getConfig();
  },
  computed: {
    isHeaderEnabled() {
      if (this.config && this.config.length) {
        return this.config.findIndex(c => c.dock.split("_")[0] === "TOP") > -1;
      }
      return false;
    },
    isFooterEnabled() {
      if (this.config && this.config.length) {
        return (
          this.config.findIndex(c => c.dock.split("_")[0] === "BOTTOM") > -1
        );
      }
      return false;
    },
    isLeftEnabled() {
      if (this.config && this.config.length) {
        return this.config.findIndex(c => c.dock.split("_")[0] === "LEFT") > -1;
      }
      return false;
    },
    isRightEnabled() {
      if (this.config && this.config.length) {
        return (
          this.config.findIndex(c => c.dock.split("_")[0] === "RIGHT") > -1
        );
      }
      return false;
    }
  },
  methods: {
    onTabSelected(dockPosition, tab) {
      if (tab.module && tab.module.router) {
        this.$router.push(`/hub/station/${tab.module.router.entry}`);
      }
    },
    onDockLoaded(dockPosition) {
      if (dockPosition) {
        const POS = dockPosition.toUpperCase();
        const dockContents = this.config.filter(
          c => c.dock.split("_")[0] === POS
        );
        if (dockContents && dockContents.length) {
          this.hubEntries.contents = dockContents;
          this.hubEntries
            .loadAssets()
            .then(() => {
              return this.hubEntries.loadEntries();
            })
            .then(() => {
              this.$nextTick(() => {
                this.content[dockPosition] = dockContents;
              });
            });
        }
      }
    },
    getConfig() {
      this.$http
        .get("/hub/config/")
        .then(response => {
          const data = response.data.data;
          this.config = data;
        })
        .catch(error => {
          // TODO Notify user
          console.error(error);
        });
    }
  }
};
