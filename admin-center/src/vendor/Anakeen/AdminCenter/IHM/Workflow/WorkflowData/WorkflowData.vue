<template>
  <div class="wfl-data">
    <kendo-datasource
      ref="wflGrid"
      :transport-read="getWfl"
      :schema-data="parseWflData"
      :server-filtering="true"
    >
    </kendo-datasource>
    <kendo-grid
      ref="wflGridContent"
      class="wfl-grid-content"
      :data-source-ref="'wflGrid'"
      :sortable="false"
      :resizable="true"
      :dataBound="gridDataBound"
      :filterable-mode="'row'"
      :filterable-extra="false"
    >
      <kendo-grid-column
        :field="'type'"
        :title="$t('AdminCenterWorkflow.Identifier')"
        :template="displayData('type')"
        :filterable-cell-operator="'contains'"
        :filterable-cell-show-operators="false"
        :filterable-cell-template="autoFilterCol"
      ></kendo-grid-column>
      <kendo-grid-column
        :field="'info'"
        :title="$t('AdminCenterWorkflow.Informations')"
        :template="displayData('info')"
        :filterable-cell-operator="'contains'"
        :filterable-cell-show-operators="false"
        :filterable-cell-template="autoFilterCol"
      ></kendo-grid-column>
    </kendo-grid>
  </div>
</template>
<script src="./WorkflowData.controller.ts" lang="ts"></script>
<style lang="scss" scoped>
@import "./WorkflowData.scss";
</style>
