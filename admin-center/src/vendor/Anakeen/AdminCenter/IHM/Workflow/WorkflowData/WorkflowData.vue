<template>
  <div class="wfl-data">
    <kendo-datasource
      ref="wflGrid"
      :transport-read="getWfl"
      :schema-data="parseWflData"
    >
    </kendo-datasource>
    <kendo-grid
      ref="wflGridContent"
      class="wfl-grid-content"
      :data-source-ref="'wflGrid'"
      :sortable="false"
      :resizable="true"
    >
      <kendo-grid-column
        :title="'<b>Type</b>'"
        :template="displayData('type')"
      ></kendo-grid-column>
      <kendo-grid-column
        :title="'<b>Informations</b>'"
        :template="displayData('info')"
      ></kendo-grid-column>
    </kendo-grid>
  </div>
</template>
<script src="./WorkflowData.controller.ts" lang="ts"></script>
<style lang="scss" scoped>
@import "./WorkflowData.scss";
</style>
