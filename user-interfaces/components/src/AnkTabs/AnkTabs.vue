<template>
  <div
    :class="{
      'ank-tabs': true,
      [`ank-tabs--${type}`]: true,
      'ank-tabs--top': true
    }"
  >
    <div :class="{ 'ank-tabs-header': true }">
      <tabs-nav
        ref="nav"
        :panes="panes"
        :class="{ 'ank-tabs-header-inner': true }"
        :on-tab-click="onTabClick"
        :on-tab-remove="onTabRemove"
        @tabListSelected="onTabListSelected"
      />
      <span
        v-if="addable"
        class="ank-tabs-new-tab"
        @click="onTabAdd"
      >
        <i class="k-icon k-i-plus"/>

      </span>
    </div>
    <div class="ank-tabs-contents">
      <slot></slot>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./AnkTabs.scss";
</style>
<!-- Global CSS -->
<style lang="scss"/>
<script lang="ts" src="./AnkTabs.component.ts"></script>
