<template>
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">call_split</i>
      <span v-if="!isDockCollapsed"> Workflow Manager</span>
    </nav>
    <template v-slot:hubContent>
      <div class="workflow-manager">
        <admin-center-workflow-manager></admin-center-workflow-manager>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/HubElement";

export default {
  name: "ank-admin-workflow-manager",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-workflow-manager": () =>
      new Promise(resolve => {
        import("../../Workflow/AdminCenterWorkflow.vue").then(Component => {
          resolve(Component.default);
        });
      })
  }
};
</script>
<style>
.workflow-manager {
  display: flex;
  flex: 1;
  flex-direction: column;
  min-height: 0;
  height: 100%;
}
</style>
