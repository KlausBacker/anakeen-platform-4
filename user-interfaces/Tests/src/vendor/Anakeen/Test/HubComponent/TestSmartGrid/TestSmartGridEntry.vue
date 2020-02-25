<template xmlns:v-slot="http://www.w3.org/1999/XSL/Transform">
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">ballot</i>
      <span v-if="!isDockCollapsed">Smart Form</span>
    </nav>
    <template v-slot:hubContent="">
      <div class="test-smart-grid">
        <div class="grid-section grid-right">
          <!--          <h1>New grid</h1>-->
          <ank-smart-element-grid
            @rowActionClick="onRowActionClick"
            collection="TST_DDUI_ALLTYPE"
            :columns="columns"
            :actions="actions"
            defaultExportButton
            defaultExpandable
            defaultShownColumns
            checkable
            :reorderable="true"
            :resizable="true"
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
import AnkSmartGridVue from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import GridExportButton from "@anakeen/user-interfaces/components/lib/AnkSmartElementGridExportButton.esm";
import AnkGridExpandButtonController from "@anakeen/user-interfaces/components/lib/AnkSmartElementGridExpandButton.esm";

export default {
  name: "ank-test-smart-grid",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "ank-smart-element-grid": AnkSmartGridVue,
    "export-button": GridExportButton,
    "ank-se-grid-expand-button": AnkGridExpandButtonController
  },

  created() {},
  data() {
    return {
      routeUrl: () => {
        return this.entryOptions.completeRoute;
      },
      columns: [
        { field: "id", property: true },
        { field: "title", property: true },
        // { field: "test_ddui_all__title" },
        { field: "test_ddui_all__longtext" },
        // { field: "test_ddui_all__htmltext" },
        { field: "test_ddui_all__account_multiple" },
        { field: "test_ddui_all__account_multiple_array" },
        // { field: "test_ddui_all__enumlist" },
        { field: "test_ddui_all__docid" },
        // { field: "test_ddui_all__account" },
        { field: "test_ddui_all__date" },
        { field: "test_ddui_all__color" },
        // { field: "test_ddui_all__image" },
        { field: "test_ddui_all__timestamp" },
        { field: "test_ddui_all__money" }
      ],
      actions: [
        { action: "display", title: "Display" },
        { action: "modify", title: "Modify", iconClass: "fa fa-edit" },
        { action: "delete", title: "Delete", iconClass: "fa fa-trash" }
      ]
    };
  },
  methods: {
    onRowActionClick(evt) {}
  }
};
</script>
