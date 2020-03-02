<template>
  <div class="smart-element-grid-filter-criteria" @keyup.enter="filter">
    <div class="smart-element-grid-filter-form-wrapper" ref="wrapper">
      <ank-smart-form
        ref="smartForm"
        @smartFieldChange="onSmartFieldChange"
        @ready="onReady"
        class="smart-element-grid-filter-form"
        :options="{ force: true }"
        :config="config"
      ></ank-smart-form>
    </div>
    <div class="smart-element-grid-filter-criteria-buttons k-action-buttons">
      <button title="Filter" class="k-button k-primary filter-dofilter" @click="filter">{{ this.$t("gridFilter.Filter") }}</button>
      <button title="Clear" class="k-button filter-cancel" @click="clear">{{ this.$t("gridFilter.Clear") }}</button>
    </div>
  </div>
</template>

<style lang="scss">
@import "./AnkGridFilter.scss";
</style>
<script lang="ts" src="./AnkGridFilter.component.ts"></script>
