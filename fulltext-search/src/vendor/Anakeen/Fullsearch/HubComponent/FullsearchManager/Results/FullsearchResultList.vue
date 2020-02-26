<template>
  <div class="fullsearch-results">
    <kendo-grid
      ref="grid"
      :cell-render="cellTemplate"
      :style="{ height: '100%' }"
      :data-items="gridItems"
      :filterable="false"
      :resizable="false"
      :reorderable="false"
      :sortable="false"
      :pageable="false"
      :groupable="false"
      :expand-field="'expanded'"
      :columns="columns"
      :selected-field="selectedField"
      @custom="onRowClick"
    >
    </kendo-grid>
  </div>
</template>

<style lang="scss">
@import "./FullsearchResultList.scss";
</style>

<script src="./FullsearchResultList.controller.js"></script>
