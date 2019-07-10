<template>
  <div class="dev-center-search-engine-panel" ref="panel">
    <div class="dev-center-search-engine-panel-input">
      <input
        ref="searchInput"
        @keyup.enter="onEnterCb"
        :value="inputValue"
        class="form-control panel-search-input"
        type="search"
        autofocus
      />
      <i class="panel-search-icon fa fa-search"></i>
    </div>
    <div class="dev-center-search-engine-grid" ref="grid"></div>
  </div>
</template>
<script src="./devSearchEnginePanel.controller.js"></script>
<style src="./devSearchEnginePanel.scss"></style>
