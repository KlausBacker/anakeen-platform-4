<template>
  <hub-element-layout class="search-engine-component">
    <div class="dev-center-search-engine">
      <div class="dev-center-search-engine-widget">
        <button
          class="dev-center-search-button k-button k-button-icontext k-primary"
          title="Search"
          @click="onButtonSearchClick"
        >
          <i class="dev-center-search-button-icon k-icon k-i-search"></i>
          <span class="dev-center-search-button-icon k-label">Search</span>
        </button>
      </div>
    </div>
    <template v-slot:hubContent>
      <div class="dev-center-search-engine-main">
        <dev-search-engine-panel v-model="filters"></dev-search-engine-panel>
      </div>
    </template>
  </hub-element-layout>
</template>
<script src="./devSearchEngine.controller.js"></script>
<style src="./devSearchEngine.scss"></style>
