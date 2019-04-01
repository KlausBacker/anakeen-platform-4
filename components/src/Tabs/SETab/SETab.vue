<template>
  <div
    class="ank-se-tab-pane"
    v-if="!lazy || loaded || active"
    v-show="active"
    role="tabpanel"
    :aria-hidden="!active"
    :id="`se-pane-${paneName}`"
    :aria-labelledby="`se-tab-${paneName}`"
  >
    <slot
      ><ank-smart-element
        class="ank-se-tab-element"
        ref="smartElement"
      ></ank-smart-element
    ></slot>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./SETab.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./SETab.component.ts"></script>
