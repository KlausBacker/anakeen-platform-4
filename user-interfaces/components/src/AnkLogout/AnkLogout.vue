<template>
  <button
    :title="title || translations.title"
    class="logout-button"
    @click="logout"
  >
    <slot name="content"><i class="fa fa-power-off"></i></slot>
  </button>
</template>

<style lang="scss" scoped>
@import "./AnkLogout.scss";
</style>

<script src="./AnkLogout.component.ts" lang="ts"></script>
