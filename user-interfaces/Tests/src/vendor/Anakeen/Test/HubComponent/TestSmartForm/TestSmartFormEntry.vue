<template xmlns:v-slot="http://www.w3.org/1999/XSL/Transform">
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">ballot</i>
      <span v-if="!isDockCollapsed">Smart Form</span>
    </nav>
    <template v-slot:hubContent="">
      <div class="test-smart-form">
        <test-smart-form/>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";

export default {
  name: "ank-test-smart-form",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "test-smart-form": () => import("../../TestSmartForm/TestSmartForm.vue")
  },

  created() {

  },
  data() {
    return {
      routeUrl: () => {
        return this.entryOptions.completeRoute;
      },
    };
  }
};
</script>
