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
        :take="currentPage.take"
        :skip="currentPage.skip"
        :total="currentPage.total"
        :pageable="pager"
        :sortable="sortable"
        :sort="currentSort"
        @sortchange="onSortChange"
        @pagechange="onPageChange"
      >
      </kendo-grid-vue>
      <div class="smart-element-grid-footer">
        <slot name="gridFooter" v-bind:gridComponent="gridInstance"> </slot>
      </div>
    </div>
  </ank-progress>
</template>
<!-- CSS to this component only -->
<style lang="scss">
@import "./AnkSEGrid.scss";
</style>
<script src="./AnkSEGrid.component.ts" lang="ts"></script>
