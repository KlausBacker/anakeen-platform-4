<template>
  <div class="ssm-parameters-grid">
    <kendo-datasource
      ref="parametersGridData"
      class="parameters-grid-data"
      :transport-read="getParameters"
      :schema-data="parseParametersData"
      :sort="[{ field: 'label', dir: 'asc' }]"
    />
    <smart-form
      v-if="haveParameters"
      ref="ssmForm"
      :config="generateSmartForm"
      :options="{ force: true }"
      @actionClick="formClickMenu"
      @ready="ssmFormReady"
      @smartFieldChange="ssmFormChange"
      @smartFieldArrayChange="ssmArrayChange"
    />
    <div v-else class="no-records">
      <div class="empty-ssm-grid">
        <div class="empty-ssm-grid-icon">
          <i class="material-icons">grid_off</i>
        </div>
        <div class="empty-ssm-grid-text">
          <span>This Smart Structure has no parameters</span>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
@import "./SmartStructureManagerParameters.scss";
</style>
<script src="./SmartStructureManagerParameters.controller.ts" lang="ts"></script>
