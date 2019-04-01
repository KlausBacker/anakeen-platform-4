<template>
  <div
    class="ank-tab-pane"
    v-if="!lazy || loaded || active"
    v-show="active"
    role="tabpanel"
    :aria-hidden="!active"
    :id="`pane-${paneName}`"
    :aria-labelledby="`tab-${paneName}`"
  >
    <slot></slot>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./Tab.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./Tab.component.ts"></script>
