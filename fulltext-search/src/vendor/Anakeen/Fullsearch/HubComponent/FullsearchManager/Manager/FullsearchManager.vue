<template>
  <div class="fullsearch-manager">
    <ank-split-panes ref="splitter" class="ank-fullsearch-splitter" vertical watch-slots>
      <search-configs class="fullsearch-config" @selected="onSelectedDomain"  splitpanes-size="30"/>
      <search-search class="fullsearch-search" :domain="domainConfig" splitpanes-size="70" />
    </ank-split-panes>
  </div>
</template>
<style lang="scss">
@import "./FullsearchManager.scss";
</style>

<script src="./FullsearchManager.controller.ts" lang="ts"></script>
