<template>
  <div :class="{ 'smart-element-grid-header': true }">
    <div class="smart-element-grid-header-content">
      <div v-if="hasSubtitle" class="smart-element-grid-header--subtitle">
        {{ subtitle }}
      </div>
      <div class="smart-element-grid-header--title">
        <div class="smart-element-grid-header-title-wrapper">
          <span class="smart-element-grid-header--text">{{ title || field }}</span>
          <div
            v-if="!!sortable"
            class="smart-element-grid-header-sort-button-wrapper"
            v-html="sortableStateIcon"
            @click="onSort"
          ></div>
        </div>
        <div
          v-if="!!columnConfig.filterable"
          ref="filterButton"
          :class="{
            'smart-element-grid-header-filter-button-wrapper': true,
            'smart-element-grid-header-filter--is-filtered': isFiltered
          }"
          @click="showFilter = !showFilter"
        >
          <i class="k-icon k-i-filter"></i>
          <Popup
            anchor="filterButton"
            :show="showFilter"
            popup-class="smart-element-grid-filter-content"
            :anchor-align="{ horizontal: 'left', vertical: 'bottom' }"
            :popup-align="{ horizontal: 'right', vertical: 'top' }"
          >
            <ank-grid-filter @clear="clearFilter" @filter="filter" v-bind="$props"></ank-grid-filter>
          </Popup>
        </div>
      </div>
    </div>
  </div>
</template>

<!-- CSS to this component only -->
<style lang="scss">

@import "./AnkGridHeaderCell.scss";
</style>

<script src="./AnkGridHeaderCell.component.ts" lang="ts"></script>
