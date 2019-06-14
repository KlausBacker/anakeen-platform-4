<template>
  <div>
    <nav v-if="isDockCollapsed">
      <i class="fa fa-flag" aria-hidden="true"></i>
    </nav>
    <nav v-else-if="isDockExpanded">
      <i class="fa fa-flag" aria-hidden="true"></i> <span>I18n</span>
    </nav>
    <div v-else-if="isHubContent" class="i18n-station">
      <admin-center-i18n
        @changeLocaleWrongArgument="handleLocaleWrongArgumentError" @i18nOffline="handleLocaleNetworkError"
      ></admin-center-i18n>
    </div>
  </div>
</template>
<script>
import Vue from "vue";
import HubElement from "@anakeen/hub-components/components/lib/HubElement";

export default {
  name: "ank-admin-i18n",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-i18n": () =>
      new Promise(resolve => {
        import("../../I18n/AdminCenterI18n.vue").then(Component => {
          resolve(Component.default);
        });
      })
  },
  methods: {
    handleLocaleWrongArgumentError(message) {
      this.hubNotify({
        type: "error",
        content: {
          textContent: message, // ou htmlContent: "<em>Un message d'information important</em>"
          title: "Wrong locale argument"
        }
      });
    },
    handleLocaleNetworkError(message) {
      this.hubNotify({
        type: "error",
        content: {
          textContent: message,
          title: "Network error"
        }
      })
    }
  }
};
</script>
<style>
.i18n-station {
  display: flex;
  flex: 1;
  flex-direction: column;
  min-height: 0;
  height: 100%;
}
</style>
