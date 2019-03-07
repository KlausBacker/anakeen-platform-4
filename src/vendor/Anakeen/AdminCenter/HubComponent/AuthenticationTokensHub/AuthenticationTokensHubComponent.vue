<template>
    <div>
        <nav v-if="isDockCollapsed">
            <i class="material-icons hub-icon">fingerprint</i>
        </nav>
        <nav v-else-if="isDockExpanded">
            <i class="material-icons hub-icon">fingerprint</i><span> Authentication Tokens</span>
        </nav>
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