<template>
  <div
    :class="{
      'ank-tabs': true,
      [`ank-tabs--${type}`]: true,
      [`ank-tabs--${tabPosition}`]: true
    }"
  >
    <div :class="{ 'ank-tabs-header': true, [`is-${tabPosition}`]: true }">
      <tabs-nav
         ref="nav"
        :panes="panes"
        :class="{ 'ank-tabs-header-inner': true, [`is-${tabPosition}`]: true }"
        :onTabClick="onTabClick"
        :onTabRemove="onTabRemove"
        @tabListSelected="onTabListSelected"
      ></tabs-nav>
      <span
        class="ank-tabs-new-tab"
        v-if="editable || addable"
        @click="onTabAdd"
      >
        <i class="k-icon k-i-plus">add</i>
      </span>
    </div>
    <div class="ank-tabs-contents">
      <slot></slot>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./AnkSETabs.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./AnkSETabs.component.ts"></script>
