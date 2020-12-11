<template>
  <ank-tabs v-model="selected" class="router-tabs">
    <ank-tab
      v-for="(tab, index) in tabs"
      :key="index"
      class="router-tab"
      :label="tab.label"
      :tab-id="tab.name"
      :lazy="true"
    >
      <slot :tab="tab"></slot>
    </ank-tab>
  </ank-tabs>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./RouterTabs.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./RouterTabs.controller.js"></script>
