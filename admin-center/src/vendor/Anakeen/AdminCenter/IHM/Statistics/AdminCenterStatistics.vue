<template>
  <div>
    <ank-tabs ref="smartTab" v-model="mySelectedTab">
      <ank-tab tab-id="activeUsers" :label="translations.activeUsers"/>
    </ank-tabs>
  </div>
</template>
<script src="./AdminCenterStatistics.controller.ts" lang="ts"></script>
<style lang="scss" scoped>
@import "./AdminCenterStatistics.scss";
</style>
