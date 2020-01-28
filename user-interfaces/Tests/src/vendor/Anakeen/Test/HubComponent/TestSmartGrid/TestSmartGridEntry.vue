<template xmlns:v-slot="http://www.w3.org/1999/XSL/Transform">
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">ballot</i>
      <span v-if="!isDockCollapsed">Smart Form</span>
    </nav>
    <template v-slot:hubContent="">
      <div class="test-smart-grid">
        <div class="grid-section grid-left">
          <h1>Old grid</h1>
          <old-grid :pageable="true" collection="DEVBILL"> </old-grid>
        </div>
        <div class="grid-section grid-right">
          <h1>New grid</h1>
          <ank-smart-element-grid
            collection="DEVBILL"
            controller="DEFAULT_GRID_CONTROLLER"
            :columns="columns"
          />
        </div>
      </div>
    </template>
  </hub-element-layout>
</template>
<style lang="scss" scoped>
.test-smart-grid {
  height: 100%;
  background: white;
  display: flex;

  .grid-section {
    flex: 1;

    & + .grid-section {
      border-left: 2px solid black;
    }
  }
}
</style>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";
import AnkSmartGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import AnkSmartGridVue from "@anakeen/user-interfaces/components/lib/AnkSmartElementVueGrid.esm";

export default {
  name: "ank-test-smart-grid",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "old-grid": AnkSmartGrid,
    "ank-smart-element-grid": AnkSmartGridVue
  },

  created() {},
  data() {
    return {
      routeUrl: () => {
        return this.entryOptions.completeRoute;
      },
      columns: [
        { field: "title", property: true },
        { field: "bill_title" },
        { field: "bill_author" },
        { field: "my_custom_column", abstract: true, withContext: true, context: ["Custom"], sortable: false }
      ]
    };
  }
};
</script>
