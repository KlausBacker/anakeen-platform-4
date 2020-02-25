<template>
  <div class="ank-progress-wrapper">
    <slot ref="slotContent"></slot>
    <div class="ank-progress-overlay" ref="overlay" v-show="loading">
      <div :class="{ 'ank-progress': true, 'ank-progress--is-loading': loading }"></div>
    </div>
  </div>
</template>

<style lang="scss" scoped>

@import "./AnkProgress.scss";
</style>

<script lang="ts" src="./AnkProgress.component.ts"></script>
