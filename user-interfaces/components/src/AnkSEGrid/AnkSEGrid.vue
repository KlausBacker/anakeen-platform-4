<template>
  <div class="smart-element-grid" ref="gridWrapper">
    <div class="smart-element-grid-header--toolbar">
      <slot name="gridHeader" direction="left" v-bind:gridComponent="gridInstance">
        <ank-columns-button v-if="defaultShownColumns" :gridComponent="gridInstance"></ank-columns-button>
        <ank-export-button
          v-if="defaultExportButton"
          :text="this.$t('gridExportButton.Title')"
          :gridComponent="gridInstance"
        ></ank-export-button>
      </slot>
    </div>
    <kendo-grid-vue
      v-if="columnsList.length"
      ref="smartGridWidget"
      class="smart-element-grid-widget"
      :columns="columnsList"
      :data-items="dataItems"
      :cell-render="cellRenderFunction"
      :header-cell-render="headerCellRenderFunction"
      :filter-cell-render="subHeaderCellRenderFunction"
      :take="currentPage.take"
      :skip="currentPage.skip"
      :total="currentPage.total"
      :resizable="resizable"
      :reorderable="reorderable"
      :pageable="false"
      :sortable="sortable"
      :sort="currentSort"
      :filterable="subHeader ? subHeader && Object.keys(subHeader).length > 0 : false"
      selectedField="ank-grid_selected_rows"
      @selectionchange="onSelectionChange"
      @sortchange="onSortChange"
      @filterchange="onFilterChange"
      @columnreorder="onColumnReorder"
    >
      <kendo-grid-norecords>
        {{ this.$t("gridComponent.No records") }}
      </kendo-grid-norecords>
    </kendo-grid-vue>
    <div class="smart-element-grid-footer">
      <slot name="gridFooter" v-bind:gridComponent="gridInstance">
        <ank-grid-pager v-if="pager" v-bind="pager" :gridComponent="gridInstance"></ank-grid-pager>
        <ank-expand-button v-if="defaultExpandable" :gridComponent="gridInstance"></ank-expand-button>
      </slot>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
@import "./AnkSEGrid.scss";
</style>
<script src="./AnkSEGrid.component.ts" lang="ts"></script>
