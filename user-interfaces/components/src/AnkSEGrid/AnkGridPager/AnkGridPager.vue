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
      <span aria-label="Go to the first page" class="k-icon k-i-seek-w" />
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
      <span aria-label="Go to the previous page" class="k-icon k-i-arrow-w" />
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
        v-for="i in maxButtonsPages"
        :key="i"
        :class="{
          'smart-element-grid-pager-pageNumber': true,
          'smart-element-grid-pager-pageNumber--selected': isCurrentPage(i)
        }"
        href="#"
        @click.prevent="goToPage(i)"
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
      <span aria-label="Go to the next page" class="k-icon k-i-arrow-e" />
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
      <span aria-label="Go to the last page" class="k-icon k-i-seek-e" />
    </a>
    <div v-if="pageSizes" class="smart-element-grid-pager-sizes" ref="gridPagerContainer">
      <input ref="gridPageSizes" :value="pageSize" class="smart-element-grid-pager-sizes--dropdown" />
      <span class="smart-element-grid-pager-sizes--label"> {{ this.$t("gridPager.items per page") }}</span>
    </div>

    <div v-if="info" class="smart-element-grid-pager-info">
      {{ this.$t("gridPager.{0} - {1} of {2} items", { 0: beginPage, 1: endPage, 2: total }) }}
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import "./AnkGridPager.scss";
</style>
<style lang="scss">
.smart-element-grid-pager {
  .smart-element-grid-pager-sizes {
    margin: 0 1rem;
    .k-widget.k-dropdown.smart-element-grid-pager-sizes--dropdown {
      width: 4rem;
    }
  }
}
.smart-element-grid-pager-sizes {
  position: relative;
}
</style>
<script lang="ts" src="./AnkGridPager.component.ts"></script>
