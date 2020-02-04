<template>
  <div class="smart-element-grid-filter-cell smart-element-grid-filter-cell--text">
    <div class="smart-element-grid-filter-cell-input">
      <text-input class="smart-element-grid-filter-cell-input--inner" @keyup.native.enter="filter(filterValue)" v-model="filterValue" />
      <div class="smart-element-grid-filter-cell-clear">
        <i
          class="smart-element-grid-filter-cell-clear-button k-icon k-i-filter-clear"
          v-if="filterValue"
          @click="reset"
        >
        </i>
      </div>
    </div>
    <dropdown-list
      class="smart-element-grid-filter-cell-operator"
      :data-items="filterOperators"
      text-field="value"
      data-item-key="key"
      v-model="selectedOperator"
    ></dropdown-list>
  </div>
</template>

<!-- CSS to this component only -->
<style lang="scss" scoped>
@import "./AnkTextFilterCell.scss";
</style>

<script src="./AnkTextFilterCell.component.ts" lang="ts"></script>
