<template>
  <div class="ssm-default-grid">
    <kendo-datasource
      ref="defaultGridData"
      class="default-grid-data"
      :transport-read="getDefaultValues"
      :schema-data="parseDefaultValuesData"
      :sort="[{ field: 'label', dir: 'asc' }]"
    />
    <kendo-grid
      ref="defaultGridContent"
      class="default-grid-content"
      :data-source-ref="'defaultGridData'"
      :sortable="false"
      :resizable="true"
      :filterable-mode="'row'"
      :filterable-extra="false"
      :no-records="{
        template: `<div class='empty-ssm-grid'>
                      <div class='empty-ssm-grid-icon'>
                        <i class='material-icons'>grid_off</i>
                      </div>
                      <div class='empty-ssm-grid-text'>
                          <span> No default values to display for this Smart Structure</span>
                      </div>
                    </div>`
      }"
    >
      <kendo-grid-column
        :title="translations.Label"
        :field="'label'"
        :filterable-cell-operator="'contains'"
        :filterable-cell-show-operators="false"
        :filterable-cell-template="autoFilterCol"
      />
      <kendo-grid-column
        :title="translations.ParentValue"
        :field="'parentValue'"
        :filterable-cell-operator="'contains'"
        :filterable-cell-show-operators="false"
        :filterable-cell-template="autoFilterCol"
        :template="displayData('parentValue')"
      />
      <kendo-grid-column
        :title="translations.RawValue"
        :field="'rawValue'"
        :filterable-cell-operator="'contains'"
        :filterable-cell-show-operators="false"
        :filterable-cell-template="autoFilterCol"
        :template="displayData('rawValue')"
      />
      <kendo-grid-column
        :title="translations.DisplayValue"
        :field="'displayValue'"
        :filterable-cell-operator="'contains'"
        :filterable-cell-show-operators="false"
        :filterable-cell-template="autoFilterCol"
        :template="displayData('displayValue')"
      />
      <kendo-grid-column :hidden="true" :title="'<b>Type</b>'" :field="'type'" />
      <kendo-grid-column :hidden="true" :title="'<b>Field ID</b>'" :field="'fieldId'" />
      <kendo-grid-column :hidden="true" :title="'<b>Parent Field ID</b>'" :field="'parentFieldId'" />
      <kendo-grid-column
        :hidden="true"
        :title="'<b>Is Advanced Value</b>'"
        :field="'isAdvancedValue'"
      />
      <kendo-grid-column :hidden="true" :title="'<b>Is Multiple</b>'" :field="'isMultiple'" />
      <kendo-grid-column :title="''" width="8rem" :command="{ click: onEditClick, text: translations.Display }" />
    </kendo-grid>

    <transition name="modal">
      <div v-if="showModal" class="modal-mask">
        <div class="modal-wrapper" @click="showModal = false">
          <div class="modal-container" @click.stop>
            <smart-form
              ref="ssmForm"
              :config="smartForm"
              :options="{ withCloseConfirmation: true }"
              @actionClick="formClickMenu"
              @ready="ssmFormReady"
              @smartFieldChange="ssmFormChange"
              @smartFieldArrayChange="ssmArrayChange"
            />
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>
<style lang="scss">
@import "./SmartStructureManagerDefaultValues.scss";
</style>
<script src="./SmartStructureManagerDefaultValues.controller.ts" lang="ts"></script>
