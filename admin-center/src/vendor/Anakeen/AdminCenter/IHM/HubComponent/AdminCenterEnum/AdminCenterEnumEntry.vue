<template>
  <hub-element-layout>
    <nav>
      <i class="fa fa-list-ol enum-icon" aria-hidden="true"></i>
      <span v-if="!isDockCollapsed">Enumerates</span>
    </nav>
    <template v-slot:hubContent>
      <div class="enum-station">
        <admin-center-enum></admin-center-enum>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHub.esm";
import AdminCenterEnum from "../../Enum/AdminCenterEnum";

export default {
  name: "ank-admin-enum-manager",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-enum": () =>
      new Promise(resolve => {
        import("../../Enum/AdminCenterEnum.vue").then(Component => {
          resolve(Component.default);
        });
      })
  }
};
</script>
<style>
.enum-station {
  display: flex;
  flex: 1;
  flex-direction: column;
  min-height: 0;
  height: 100%;
}

.enum-icon {
  font-size: 24px;
  margin-right: 0.5rem;
}
</style>
