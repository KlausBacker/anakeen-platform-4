<template>
  <div class="smart-element-grid-pager">
    <a
      v-if="previousNext"
      :class="{
        'smart-element-grid-pager-nav': true,
        'smart-element-grid-pager-first': true,
        'smart-element-grid-pager-nav--disabled': !hasPrevious
      }"
      href="#"
      @click.prevent="firstPage"
    >
      <span aria-label="Go to the first page" class="k-icon k-i-seek-w"></span>
    </a>
    <a
      v-if="previousNext"
      :class="{
        'smart-element-grid-pager-nav': true,
        'smart-element-grid-pager-prev': true,
        'smart-element-grid-pager-nav--disabled': !hasPrevious
      }"
      href="#"
      @click.prevent="previousPage"
    >
      <span aria-label="Go to the previous page" class="k-icon k-i-arrow-w"></span>
    </a>
    <div class="smart-element-grid-pager-numbers">
      <a
        v-if="hasPreviousNumbersList"
        :class="{
          'smart-element-grid-pager-pageNumber': true
        }"
        href="#"
        @click.prevent="previousNumbersList()"
      >
        ...
      </a>
      <a
        :class="{
          'smart-element-grid-pager-pageNumber': true,
          'smart-element-grid-pager-pageNumber--selected': isCurrentPage(i)
        }"
        href="#"
        v-for="i in maxButtonsPages"
        @click.prevent="goToPage(i)"
        :key="i"
      >
        {{ i }}
      </a>
      <a
        v-if="hasNextNumbersList"
        :class="{
          'smart-element-grid-pager-pageNumber': true
        }"
        href="#"
        @click.prevent="nextNumbersList()"
      >
        ...
      </a>
    </div>
    <a
      v-if="previousNext"
      :class="{
        'smart-element-grid-pager-nav': true,
        'smart-element-grid-pager-next': true,
        'smart-element-grid-pager-nav--disabled': !hasNext
      }"
      href="#"
      @click.prevent="nextPage"
    >
      <span aria-label="Go to the next page" class="k-icon k-i-arrow-e"></span>
    </a>
    <a
      v-if="previousNext"
      :class="{
        'smart-element-grid-pager-nav': true,
        'smart-element-grid-pager-last': true,
        'smart-element-grid-pager-nav--disabled': !hasNext
      }"
      href="#"
      @click.prevent="lastPage"
    >
      <span aria-label="Go to the last page" class="k-icon k-i-seek-e"></span>
    </a>

    <div class="smart-element-grid-pager-sizes" v-if="pageSizes">
      <drop-down-list
        class="smart-element-grid-pager-sizes--dropdown"
        v-model="pageSize"
        :data-items="pageSizes"
      ></drop-down-list>
      <span class="smart-element-grid-pager-sizes--label">items per page</span>
    </div>

    <div v-if="info" class="smart-element-grid-pager-info">{{ beginPage }} - {{ endPage }} of {{ total }} items</div>
  </div>
</template>

<style lang="scss" scoped>
@import "./AnkGridPager.scss";
</style>

<script lang="ts" src="./AnkGridPager.component.ts"></script>
