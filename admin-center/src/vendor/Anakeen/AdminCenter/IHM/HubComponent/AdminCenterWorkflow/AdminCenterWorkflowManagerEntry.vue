<template>
    <div>
        <nav v-if="isDockCollapsed">
            <i class="material-icons hub-icon">call_split</i>
        </nav>
        <nav v-else-if="isDockExpanded">
            <i class="material-icons hub-icon">call_split</i> <span> Workflow Manager</span>
        </nav>
        <div v-else-if="isHubContent" class="workflow-manager">
            <admin-center-workflow-manager></admin-center-workflow-manager>
        </div>
    </div>
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