<template>
  <div class="fullsearch-list">

    <kendo-grid
      ref="grid"
      :cell-render="cellTemplate"
      :style="{ height: '100%' }"
      :data-items="gridData"
      :filterable="false"
      :resizable="false"
      :reorderable="false"
      :sortable="false"
      :pageable="false"
      :groupable="false"
      :group="group"
      :expand-field="'expanded'"
      :columns="columns"
      :selected-field="selectedField"
      @expandchange="expandChange"
      @selected="onRowClick"
    >
    </kendo-grid>
  </div>
</template>

<style lang="scss">
@import "./FullsearchExpandList.scss";
</style>

<script src="./FullsearchExpandList.controller.js" ></script>
