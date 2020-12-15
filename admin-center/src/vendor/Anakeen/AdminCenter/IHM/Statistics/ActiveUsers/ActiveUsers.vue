<template>
  <div class="statistics">
    <h2 class="statistics-title">{{ $t("AdminCenterInfos.Active users over the period") }}</h2>
    <div class="statistics-period">
      <div class="statistics-date">
        <span>{{ $t("AdminCenterInfos.From") }}</span>
        <input ref="monthPickerFrom" title="month from" />
      </div>
      <div class="statistics-date">
        <span>{{ $t("AdminCenterInfos.To") }}</span>
        <input ref="monthPickerTo" title="month to" />
      </div>
      <span class="statistics-total">{{ $t("AdminCenterInfos.Total") }} : {{ activeUsersTotal }}</span>
      <kendo-button class="k-primary statistics-export" @click="exportActiveUsers">{{ $t("AdminCenterInfos.Export") }}</kendo-button>
    </div>
  </div>
</template>
<script src="./ActiveUsers.controller.ts" lang="ts"></script>
<style lang="scss" scoped>
@import "./ActiveUsers.scss";
</style>
