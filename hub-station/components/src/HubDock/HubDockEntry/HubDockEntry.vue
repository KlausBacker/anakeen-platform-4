<template>
  <div
    :class="{
      'hub-dock-entry-component': true,
      'entry-selected': entrySelected,
      'entry-selectable': selectable,
      'is-collapsed': isCollapsed,
      'is-expanded': !isCollapsed
    }"
    :style="entryStyle"
    :data-entry-name="name"
    @click="selectEntry"
  >
    <slot></slot>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./HubDockEntry.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./HubDockEntry.component.ts"></script>
