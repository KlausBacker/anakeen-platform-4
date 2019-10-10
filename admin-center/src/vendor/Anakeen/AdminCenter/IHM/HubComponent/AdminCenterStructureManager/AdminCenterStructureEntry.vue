<template>
    <hub-element-layout>
        <nav>
            <i class="material-icons hub-icon">code</i>
            <span v-if="!isDockCollapsed"> Smart Structure Manager</span>
        </nav>
        <template v-slot:hubContent>
            <div class="structure-parent">
                <admin-center-structure></admin-center-structure>
            </div>
        </template>
    </hub-element-layout>
</template>
<script>
  import HubElement from "@anakeen/hub-components/components/lib/HubElement";

  export default {
    name: "ank-admin-structure",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      "admin-center-structure": resolve =>
        import("../../SmartStructureManager/AdminCenterStructure.vue").then(Component => {
          resolve(Component.default);
        })
    }
  };
</script>
<style>
    .structure-parent {
        height: 100%;
        width: 100%;
    }
</style>
