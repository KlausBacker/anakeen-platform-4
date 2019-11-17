<template>
  <div class="ssm-default-grid">
    <kendo-datasource ref="defaultGridData"
                      class="default-grid-data"
                      :transport-read="getDefaultValues"
                      :schema-data="parseDefaultValuesData"
                      :sort="[{ field: 'label', dir: 'asc'}]">
    </kendo-datasource>
    <kendo-grid
      ref="defaultGridContent"
      class="default-grid-content"
      :data-source-ref="'defaultGridData'"
      :sortable="false"
      :resizable="true"
      :filterable-mode="'row'"
      :filterable-extra="false"
      :noRecords="{ template: `<div class='empty-ssm-grid'>
                                                <div class='empty-ssm-grid-icon'>
                                                   <i class='material-icons'>grid_off</i>
                                                </div>
                                                <div class='empty-ssm-grid-text'>
                                                    <span> No default values to display for this Smart Structure</span>
                                                </div>
                                            </div>`}">
      <kendo-grid-column :title="'<b>Label</b>'" :field="'label'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" ></kendo-grid-column>
      <kendo-grid-column :hidden=true :title="'<b>Type</b>'" width="10rem" :field="'type'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" ></kendo-grid-column>
      <kendo-grid-column :title="'<b>Value</b>'" :field="'value'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" :template="displayData('value')"></kendo-grid-column>
      <kendo-grid-column :title="''" width="6.5rem" :command="{click:onEditClick, text: 'Modify' }"></kendo-grid-column>
    </kendo-grid>
    <modal name="ssm-modal" width="50%" height="50%" @before-open="beforeEdit">
      <smart-form :config="smartForm" @actionClick="formClickMenu" ref="ssmForm" @ready="ssmFormReady" @smartFieldChange="ssmFormChange"></smart-form>
    </modal>
  </div>
</template>
<style lang="scss">
@import "./SmartStructureManagerDefaultValues.scss";
</style>
<script src="./SmartStructureManagerDefaultValues.controller.ts" lang="ts"></script>
