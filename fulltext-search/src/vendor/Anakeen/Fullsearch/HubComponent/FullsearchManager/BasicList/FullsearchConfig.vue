<template>
  <div class="fullsearch-list">

    <kendo-grid
      ref="grid"
      :detail="detailTemplate"
      :style="{ height: '100%' }"
      :data-items="gridData"
      :filterable="false"
      :resizable="false"
      :reorderable="false"
      :sortable="false"
      :pageable="false"
      :groupable="false"
      :expand-field="'expanded'"
      :columns="columns"
      :selected-field="selectedField"
      @rowclick="onRowClick"
      @expandchange="expandChange"
    >
    </kendo-grid>
  </div>
</template>

<style lang="scss">
@import "./FullsearchConfig.scss";
</style>

<script src="./FullsearchConfig.controller.js" ></script>
