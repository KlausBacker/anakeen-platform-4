<template>
  <hub-element-layout>
    <nav>
      <i class="fa fa-info-circle rd-icon" aria-hidden="true"></i>
      <span v-if="!isDockCollapsed">{{ $t("AdminCenterRenderDescription.Title Render Description") }}</span>
    </nav>
    <template v-slot:hubContent>
      <div class="rd-station">
        <admin-center-render-description></admin-center-render-description>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";

export default {
  name: "ank-admin-render-description-manager",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-render-description": () => import("../../RenderDescription/AdminCenterRenderDescription.vue")
  }
};
</script>
<style>
.rd-station {
  display: flex;
  flex: 1;
  flex-direction: column;
  min-height: 0;
  height: 100%;
}

.rd-icon {
  font-size: 24px;
  margin-right: 0.5rem;
}
</style>
