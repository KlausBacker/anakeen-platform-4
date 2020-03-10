<template>
  <div class="fullsearch-manager">
    <ank-split-panes ref="splitter" class="ank-fullsearch-splitter" vertical watch-slots>
      <search-configs class="fullsearch-config" @selected="onSelectedDomain"  splitpanes-size="30"/>
      <search-search class="fullsearch-search" :domain="domainConfig" splitpanes-size="70" />
    </ank-split-panes>
  </div>
</template>
<style lang="scss">
@import "./FullsearchDevel.scss";
</style>

<script src="./FullsearchDevel.controller.ts" lang="ts"></script>
