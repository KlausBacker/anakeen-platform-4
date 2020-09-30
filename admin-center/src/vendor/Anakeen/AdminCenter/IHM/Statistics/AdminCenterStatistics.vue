<template>
  <div class="statistics-plugin">
    <ank-tabs ref="smartTab" v-model="mySelectedTab">
      <ank-tab tab-id="activeUsers" :label="translations.activeUsers">
        <active-users></active-users>
      </ank-tab>
    </ank-tabs>
  </div>
</template>
<script src="./AdminCenterStatistics.controller.ts" lang="ts"></script>
<style lang="scss" scoped>
@import "./AdminCenterStatistics.scss";
</style>
