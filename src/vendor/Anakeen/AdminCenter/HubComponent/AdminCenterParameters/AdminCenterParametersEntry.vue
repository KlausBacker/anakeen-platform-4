<template>
    <div>
        <nav v-if="isDockCollapsed">
            <i class="material-icons hub-icon">settings</i>
        </nav>
        <nav v-else-if="isDockExpanded">
            <i class="material-icons hub-icon">settings</i><span> Parameters</span>
        </nav>
        <div v-else-if="isHubContent" class="parameters-parent">
            <admin-center-parameters></admin-center-parameters>
        </div>
    </div>
</template>
<script>
import AdminCenterParameters from "../../Parameters/AdminCenterParameters.vue";
import { HubElement } from "@anakeen/hub-components";

export default {
  name: "ank-admin-parameter",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-parameters": AdminCenterParameters
  }
};
</script>
<style>
    .parameters-parent {
        height: 100%;
    }
</style>