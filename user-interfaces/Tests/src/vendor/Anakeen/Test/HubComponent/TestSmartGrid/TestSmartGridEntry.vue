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
          <old-grid :pageable="true" collection="TST_DDUI_ALLTYPE" :checkable="true">
            <!-- <old-grid-column field="test_ddui_all__title"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__longtext"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__htmltext"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__integer"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__double"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__money"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__docid"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__account"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__date"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__time"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__timestamp"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__password"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__color"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__file"></old-grid-column> -->
            <!-- <old-grid-column field="test_ddui_all__image"></old-grid-column> -->
            <old-grid-column field="test_ddui_all__account_multiple"></old-grid-column>
            <old-grid-column field="test_ddui_all__account_multiple_array"></old-grid-column>
            <old-grid-column field="test_ddui_all__enumlist"></old-grid-column>
            <!-- xml -->
            <!-- json -->
          </old-grid>
        </div>
        <div class="grid-section grid-right">
          <h1>New grid</h1>
          <ank-smart-element-grid
            @rowActionClick="onRowActionClick"
            collection="TST_DDUI_ALLTYPE"
            :columns="columns"
            :actions="actions"
            :reorderable="true"
            :resizable="true"
            :footer="footer"
          >
          </ank-smart-element-grid>
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

  .grid-section.grid-right {
    flex: 2;
  }

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
        { field: "test_ddui_all__title" },
        // { field: "test_ddui_all__longtext" },
        // { field: "test_ddui_all__htmltext" },
        //{ field: "test_ddui_all__account_multiple" },
        //{ field: "test_ddui_all__account_multiple_array" },
        //{ field: "test_ddui_all__enumlist" },
        { field: "test_ddui_all__docid" },
        { field: "test_ddui_all__account" },
        { field: "test_ddui_all__date" },
        { field: "test_ddui_all__color" },
        // { field: "test_ddui_all__image" },
        // { field: "test_ddui_all__timestamp" },
        { field: "test_ddui_all__money" }
        // { field: "test_ddui_all__money" },
        // { field: "bill_billdate" },
        // { field: "bill_cost" }
      ],
      footer: {
        title: "Un superbe footer de colonne",
        test_ddui_all__money: "On pourrait presque faire la somme là"
      },
      actions: [
        { action: "consult", title: "Consult" },
        { action: "edit", title: "Edit", iconClass: "fa fa-edit" },
        { action: "delete", title: "Delete", iconClass: "fa fa-trash" }
      ]
    };
  },
  methods: {
    onRowActionClick(evt) {}
  }
};
</script>
