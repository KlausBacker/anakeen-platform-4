<template>
  <div
ref="headerWrapper" :class="{ 'smart-element-grid-header': true }"
>
    <div class="smart-element-grid-header-content">
      <div v-show="hasSubtitle" class="smart-element-grid-header--subtitle">
        {{ subtitle }}
      </div>
      <div class="smart-element-grid-header--title">
        <div class="smart-element-grid-header-title-wrapper">
          <span class="smart-element-grid-header--text">{{ title || field }}</span>
          <div
            v-if="!!sortable"
            class="smart-element-grid-header-sort-button-wrapper"
            @click="onSort"
            v-html="sortableStateIcon"
          />
        </div>
        <div
          v-if="!!columnConfig.filterable"
          ref="filterButton"
          :class="{
            'smart-element-grid-header-filter-button-wrapper': true,
            'smart-element-grid-header-filter--is-filtered': isFiltered
          }"
          style="width: 5rem"
        >
          <i class="k-icon k-i-filter smart-element-grid-filter-icon" @click="showFilters" />
        </div>
      </div>
    </div>
    <popup
      ref="filterPopup"
      class="smart-element-grid-filter-content"
      :show="showFilter"
      :offset="filterOffset"
    >
      <ank-grid-filter ref="filterElement" v-bind="$props" @clear="clearFilter" @filter="filter" />
    </popup>
  </div>
</template>

<!-- CSS to this component only -->
<style lang="scss">
@import "./AnkGridHeaderCell.scss";
</style>

<script src="./AnkGridHeaderCell.component.ts" lang="ts"></script>
