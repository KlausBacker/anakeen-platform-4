<template>
  <div class="ssm-parameters-grid">
    <kendo-datasource
      ref="parametersGridData"
      class="parameters-grid-data"
      :transport-read="getParameters"
      :schema-data="parseParametersData"
      :sort="[{ field: 'label', dir: 'asc' }]"
    />
    <!-- <kendo-grid
      ref="parametersGridContent"
      class="parameters-grid-content"
      :data-source-ref="'parametersGridData'"
      :sortable="false"
      :resizable="true"
      :filterable-mode="'row'"
      :filterable-extra="false"
      :noRecords="{ template: `<div class='empty-ssm-grid'>
                                                <div class='empty-ssm-grid-icon'>
                                                   <i class='material-icons'>grid_off</i>
                                                </div>
                                                <div class='empty-ssm-grid-text'>
                                                    <span> No parameters to display for this Smart Structure</span>
                                                </div>
                                            </div>`}">
      <kendo-grid-column :title="'<b>Label</b>'" :field="'label'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" ></kendo-grid-column>
      <kendo-grid-column :title="'<b>Parent Value</b>'" :field="'parentValue'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" :template="displayData('parentValue')"></kendo-grid-column>
      <kendo-grid-column :title="'<b>Raw Value</b>'" :field="'rawValue'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" :template="displayData('rawValue')"></kendo-grid-column>
      <kendo-grid-column :title="'<b>Display Value</b>'" :field="'displayValue'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" :template="displayData('displayValue')"></kendo-grid-column>
      <kendo-grid-column :hidden="true" :title="'<b>Type</b>'" :field="'type'"></kendo-grid-column>
      <kendo-grid-column :hidden="true" :title="'<b>Field ID</b>'" :field="'parameterId'"></kendo-grid-column>
      <kendo-grid-column :title="''" width="6.5rem" :command="{click:onEditClick, text: 'Modify'}"></kendo-grid-column>
    </kendo-grid> -->
    <smart-form
      ref="ssmForm"
      :config="generateSmartForm"
      @actionClick="formClickMenu"
      @ready="ssmFormReady"
      @smartFieldChange="ssmFormChange"
    />
    <!-- <transition name="modal">
      <div v-show="showModal" class="modal-mask">
        <div class="modal-wrapper" @click="showModal = false">
          <div class="modal-container" @click.stop>
            <smart-form :config="smartForm" @actionClick="formClickMenu" ref="ssmForm" @ready="ssmFormReady" @smartFieldChange="ssmFormChange"></smart-form>
          </div>
        </div>
      </div>
    </transition> -->
  </div>
</template>
<style lang="scss">
@import "./SmartStructureManagerParameters.scss";
</style>
<script src="./SmartStructureManagerParameters.controller.ts" lang="ts"></script>
