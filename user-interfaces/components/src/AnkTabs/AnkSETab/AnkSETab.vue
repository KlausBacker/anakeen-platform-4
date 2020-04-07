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
        :autoUnload="autoUnload"
        :customClientData="customClientData"
        :browserHistory="browserHistory"
        class="ank-se-tab-element"
        ref="smartElement"
        @smartElementMounted="onSmartElementLoaded"
      >
        <template v-if="hasLoadingSlot" v-slot:loading>
          <slot name="loading"></slot>
        </template>
      </ank-smart-element>
    </slot>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./AnkSETab.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./AnkSETab.component.ts"></script>