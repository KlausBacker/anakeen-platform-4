<template>
    <div>
        <div v-if="isDockCollapsed">
            <i class="fa fa-cog"></i>
        </div>
        <div v-else-if="isDockExpanded">
            <span>Admin Center Parameters</span>
        </div>
        <div v-else-if="isHubContent">
            <ank-admin-parameters></ank-admin-parameters>
        </div>
    </div>
</template>
<script>
  import AdminCenterParameters from "../../Parameters/AdminCenterParameters.vue";
  import { HubElement } from "@anakeen/hub-components";

  export default {
    name: "ank-admin-parameters",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      AdminCenterParameters
    }
  }
</script>