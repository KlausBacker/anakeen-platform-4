<template>
    <div>
        <div v-if="isDockCollapsed">
            <i class="fa fa-cog"></i>
        </div>
        <div v-else-if="isDockExpanded">
            <span><i class="fa fa-cog"></i> Parameters</span>
        </div>
        <div v-else-if="isHubContent">
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
  }
</script>