<template>
  <div ref="panel" class="dev-center-search-engine-panel">
    <div class="dev-center-search-engine-panel-input">
      <input
        ref="searchInput"
        :value="inputValue"
        class="form-control panel-search-input"
        type="search"
        autofocus
        @keyup.enter="onEnterCb"
      />
      <i class="panel-search-icon fa fa-search"></i>
    </div>
    <div ref="grid" class="dev-center-search-engine-grid"></div>
  </div>
</template>
<script src="./devSearchEnginePanel.controller.js"></script>
<style src="./devSearchEnginePanel.scss"></style>
