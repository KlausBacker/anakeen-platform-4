<template>
  <div class="ssm-default-grid">
    <kendo-datasource ref="defaultGridData"
                      :transport-read="getDefaultValues"
                      :schema-data="parseDefaultValuesData">
    </kendo-datasource>
    <kendo-grid
      ref="defaultGridContent"
      class="default-grid-content"
      :data-source-ref="'defaultGridData'"
      :sortable="false"
      :resizable="true"
      :noRecords="{ template: `<span> LOL </span>` }"
    >
      <kendo-grid-column :title="'<b>Id</b>'" :field="'id'"></kendo-grid-column>
      <kendo-grid-column :title="'<b>Type</b>'" :field="'type'"></kendo-grid-column>
      <kendo-grid-column :title="'<b>Value</b>'" :template="displayData('value')"></kendo-grid-column>
      <kendo-grid-column :title="''" :command="{name:'edit', click:onEditClick }"></kendo-grid-column>
    </kendo-grid>
    <modal name="hello-world" width="50%" height="50%" @before-open="beforeEdit">
      <smart-form :config="smartForm"></smart-form>
    </modal>
  </div>
</template>
<style lang="scss">
@import "./SmartStructureManagerDefaultValues.scss";
</style>
<script src="./SmartStructureManagerDefaultValues.controller.ts" lang="ts"></script>
