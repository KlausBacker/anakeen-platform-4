<template>
  <hub-element-layout>
    <nav>
      <span>Hub</span>
    </nav>
    <template v-slot:hubContent>
      <div class="dev-hub-instanciation">
        <dev-hub-instanciation
          :hubInstanceSelected="hubInstanceSelected"
          @hubInstanceSelected="onHubInstanceSelected"
          :hubComponentSelected="hubComponentSelected"
          @hubComponentSelected="onHubComponentSelected"
        ></dev-hub-instanciation>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/HubElement";
import { setupVue } from "../../setup.js";

export default {
  name: "ank-dev-hub-instanciation",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "dev-hub-instanciation": () =>
      new Promise(resolve => {
        import("../../sections/Hub/HubAdminInstanciation/HubAdminInstanciation.vue").then(Component => {
          resolve(Component.default);
        });
      })
  },
  data() {
    return {
      hubInstanceSelected: 0,
      hubComponentSelected: 0
    };
  },
  watch: {
    hubInstanceSelected(value) {
      this.navigate(`${this.entryOptions.completeRoute}/${this.url}`);
    },
    hubComponentSelected(value) {
      this.navigate(`${this.entryOptions.completeRoute}/${this.url}`);
    }
  },
  beforeCreate() {
    this.$parent.$parent.collapsable = false;
    this.$parent.$parent.collapsed = false;
  },
  computed: {
    url() {
      let url = "";
      if (this.hubInstanceSelected) {
        url += `${this.hubInstanceSelected}/`;
        if (this.hubComponentSelected) {
          url += this.hubComponentSelected;
        }
      }
      return url;
    }
  },
  created() {
    setupVue(this);
    const pattern = `/${this.entryOptions.route}(?:/(?:(\w+)(?:/(?:(\w+))?)?)?)?`;
    this.getRouter()
      .on({
        [`${this.entryOptions.route}/:hubInstance/:hubComponent`]: (params) => {
          this.hubInstanceSelected = params.hubInstance;
          this.hubComponentSelected = params.hubComponent;
        },
        [`${this.entryOptions.route}/:hubInstance`]: (param) => {
          this.hubInstanceSelected = param.hubInstance;
        }
      })
      .resolve();
  },
  methods: {
    onHubInstanceSelected(hubInstance) {
      this.hubInstanceSelected = hubInstance;
    },
    onHubComponentSelected(hubComponent) {
      this.hubComponentSelected = hubComponent;
    },
  }
};
</script>
<style>
.dev-hub-instanciation {
  height: 100%;
  width: 100%;
}
</style>
