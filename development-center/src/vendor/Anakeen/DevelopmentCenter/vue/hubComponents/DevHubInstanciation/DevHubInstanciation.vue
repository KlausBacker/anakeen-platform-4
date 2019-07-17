<template>
  <hub-element-layout>
    <nav>
      <span>Hub</span>
    </nav>
    <template v-slot:hubContent>
      <div class="dev-hub-instanciation">
        <dev-hub-instanciation></dev-hub-instanciation>
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
        import(
          "../../sections/Hub/HubAdminInstanciation/HubAdminInstanciation.vue"
        ).then(Component => {
          resolve(Component.default);
        });
      })
  },
  beforeCreate() {
    this.$parent.$parent.collapsable = false;
    this.$parent.$parent.collapsed = false;
  },
  created() {
    setupVue(this);
  }
};
</script>
<style>
.dev-hub-instanciation {
  height: 100%;
  width: 100%;
}
</style>
