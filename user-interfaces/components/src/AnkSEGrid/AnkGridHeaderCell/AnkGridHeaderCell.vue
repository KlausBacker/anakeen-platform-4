<template>
  <div :class="{ 'smart-element-grid-header': true }">
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
        >
          <i class="k-icon k-i-filter" @click="showFilter = !showFilter" />
          <Popup
            ref="filterPopup"
            :anchor="'filterButton'"
            :show="showFilter"
            popup-class="smart-element-grid-filter-content"
            :anchor-align="{ horizontal: 'left', vertical: 'bottom' }"
            :popup-align="{ horizontal: 'right', vertical: 'top' }"
            :collision="collision"
          >
            <ank-grid-filter v-bind="$props" @clear="clearFilter" @filter="filter" />
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
