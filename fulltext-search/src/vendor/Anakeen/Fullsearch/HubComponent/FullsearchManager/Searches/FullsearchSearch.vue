<template>
  <div class="fullsearch-search">
    <div v-if="configs" class="search-inputs-parent">
      <ank-split-panes v-if="domain" class="ank-fullsearch-splitter" vertical watch-slots>
        <div splitpanes-size="40" class="search-pane">
          <header>
            <h1 v-if="domain">
              {{ $t("AdminCenterFullsearch.Searching in") }}<b> {{ domain }}</b>
            </h1>
            <div v-if="domain" class="search-inputs">
              <span class="k-textbox k-space-right" style="width: 100%;">
                <input v-model="pattern" type="search" placeholder="Enter words to search" @keyup.enter="search" />
                <a class="k-icon k-i-search">&nbsp;</a>
              </span>
              <kendo-button class="k-primary" @click="search">Search</kendo-button>
            </div>
          </header>
          <search-results :data-items="elementList" :pattern="searchPattern" @selected="selectElement" />
        </div>
        <ank-smart-element
          v-show="selectedElement"
          class="fullsearch-element"
          :initid="selectedElement"
          splitpanes-size="60"
        />
      </ank-split-panes>
      <div v-else class="fullsearch-domain-empty">
        <span class="fa fa-search fullsearch-domain-empty-icon"></span>
        <span class="fullsearch-domain-empty-text">{{ $t("AdminCenterFullsearch.Select Domain First") }}</span>
      </div>
    </div>
    <div v-else class="fullsearch-config-empty">
      <span class="fa fa-search fullsearch-config-empty-icon"></span>
      <span class="fullsearch-config-empty-text">{{ $t("AdminCenterFullsearch.No domain configured") }}</span>
    </div>
  </div>
</template>

<style lang="scss">
@import "./FullsearchSearch.scss";
</style>

<script src="./FullsearchSearch.controller.ts" lang="ts"></script>
