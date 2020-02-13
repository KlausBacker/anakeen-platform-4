<template>
  <ank-progress class="smart-element-grid" :loading="isLoading">
    <div class="smart-element-grid-wrapper">
      <div class="smart-element-grid-header">
        <slot name="gridHeader" direction="left" v-bind:gridComponent="gridInstance">
          <ank-export-button text="Export" :gridComponent="gridInstance"></ank-export-button>
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
        :take="currentPage.take"
        :skip="currentPage.skip"
        :total="currentPage.total"
        :resizable="resizable"
        :reorderable="reorderable"
        :pageable="false"
        :sortable="sortable"
        :sort="currentSort"
        :filterable="filterable"
        @sortchange="onSortChange"
        @filterchange="onFilterChange"
        @columnreorder="onColumnReorder"
      >
      </kendo-grid-vue>
      <kendo-grid-vue
        class="smart-element-grid-columns-footer"
        v-if="footerData && footerData.length && columnsList.length"
        :columns="columnsList"
        :data-items="footerData"
      ></kendo-grid-vue>
      <div class="smart-element-grid-footer">
        <slot name="gridFooter" v-bind:gridComponent="gridInstance">
          <ank-grid-pager v-if="pager" v-bind="pager" :gridComponent="gridInstance"></ank-grid-pager>
        </slot>
      </div>
    </div>
  </ank-progress>
</template>
<!-- CSS to this component only -->
<style lang="scss">
@import "./AnkSEGrid.scss";

</style>
<script src="./AnkSEGrid.component.ts" lang="ts"></script>
