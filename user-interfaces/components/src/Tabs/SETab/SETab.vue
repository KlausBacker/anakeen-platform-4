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
    <slot>
      <ank-smart-element
        v-show="smartElementLoaded"
        :initid="identifier"
        :viewId="viewId"
        :revision="revision"
        class="ank-se-tab-element"
        ref="smartElement"
        @documentLoaded="onSmartElementLoaded"
      ></ank-smart-element>
      <ank-loading
        class="ank-loading"
        :fullLabel="false"
        height="30px"
        fontColor="black"
        v-if="!smartElementLoaded"
      ></ank-loading>
    </slot>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./SETab.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./SETab.component.ts"></script>
