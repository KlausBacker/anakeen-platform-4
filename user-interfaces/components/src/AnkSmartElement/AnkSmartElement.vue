<template>
  <div class="ankSEWrapper" ref="ankSEWrapper">

    <div v-if="true" class="smart-element-custom-loading">
      <slot name="loading"></slot>
    </div>
  </div>
</template>

<script lang="ts" src="./AnkSmartElement.component.ts"></script>

<style lang="scss" scoped>
@import "./AnkSmartElement.scss";
</style>
