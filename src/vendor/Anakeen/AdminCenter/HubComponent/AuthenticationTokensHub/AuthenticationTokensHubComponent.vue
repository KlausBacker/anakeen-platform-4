<template>
    <div>
        <div v-if="isDockCollapsed">
            <i class="fa fa-user-secret"></i>
        </div>
        <div v-else-if="isDockExpanded">
            <span><i class="fa fa-user-secret"></i> Authentication Tokens</span>
        </div>
        <div v-else-if="isHubContent" class="token-station">
            <admin-center-authentication-tokens></admin-center-authentication-tokens>
        </div>
    </div>
</template>
<script>
  import AdminCenterAuthentTokens from "../../AuthenticationTokens/AuthenticationTokens";
  import { HubElement } from "@anakeen/hub-components";

  export default {
    name: "ank-hub-authentication-tokens",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
       "admin-center-authentication-tokens": AdminCenterAuthentTokens
    }
  }
</script>