<template>
  <div class="fullsearch-manager">
    <ank-split-panes watch-slots vertical ref="splitter" class="ank-fullsearch-splitter">
      <search-configs class="fullsearch-config" />
      <search-search  class="fullsearch-search" :domain="domainConfig" />
    </ank-split-panes>
  </div>
</template>
<style lang="scss">
@import "./FullsearchManager.scss";
</style>

<script src="./FullsearchManager.controller.ts" lang="ts"></script>
