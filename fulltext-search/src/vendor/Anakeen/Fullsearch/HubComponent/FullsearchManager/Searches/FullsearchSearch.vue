<template>
  <div class="fullsearch-search">
    <ank-split-panes v-if="domain" class="ank-fullsearch-splitter" vertical watch-slots>
      <div splitpanes-size="40" class="search-pane">
        <header>
          <h1 v-if="domain">
            Searching in <b>{{ domain }}</b>
          </h1>

          <div v-if="domain" class="search-inputs">

              <span class="k-textbox k-space-right" style="width: 100%;" >
                     <input
                             v-model="pattern"
                             type="search"
                             placeholder="Enter words to search"
                             @keyup.enter="search"
                     />
                    <a class="k-icon k-i-search">&nbsp;</a>
                </span>
            <kendo-button class="k-primary" @click="search">Search</kendo-button>
          </div>
        </header>
        <search-results :data-items="elementList" :pattern="searchPattern" @selected="selectElement" />
      </div>
      <ank-smart-element class="fullsearch-element" :initid="selectedElement" v-show="selectedElement" splitpanes-size="60"/>
    </ank-split-panes>
    <h1 v-else>
      <b>Select Domain First</b>
    </h1>
  </div>
</template>

<style lang="scss">
@import "./FullsearchSearch.scss";
</style>

<script src="./FullsearchSearch.controller.ts" lang="ts"></script>
