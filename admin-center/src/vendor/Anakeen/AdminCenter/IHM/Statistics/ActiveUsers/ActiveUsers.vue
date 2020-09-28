<template>
  <div class="statistics">
    <h2 class="statistics-title">{{ $t("AdminCenterStatistics.Active users over the period") }}</h2>
    <div class="statistics-period">
      <span>{{ $t("AdminCenterStatistics.From") }}</span>
      <input ref="monthPickerFrom" title="month from" />
      <span>{{ $t("AdminCenterStatistics.To") }}</span>
      <input ref="monthPickerTo" title="month to" />
      <span class="statistics-total">{{ $t("AdminCenterStatistics.Total") }} : {{ activeUsersTotal }}</span>
      <kendo-button class="k-primary" @click="exportActiveUsers">{{ $t("AdminCenterStatistics.Export") }}</kendo-button>
    </div>
  </div>
</template>
<script src="./ActiveUsers.controller.ts" lang="ts"></script>
<style lang="scss" scoped>
@import "./ActiveUsers.scss";
</style>
