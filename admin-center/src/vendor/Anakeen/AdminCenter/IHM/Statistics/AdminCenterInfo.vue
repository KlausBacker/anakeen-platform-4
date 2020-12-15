<template>
  <div class="infos-plugin">
    <ank-tabs ref="smartTab" v-model="mySelectedTab">
      <ank-tab tab-id="about" :label="translations.about" >
        <about />
      </ank-tab>
      <ank-tab tab-id="activeUsers" :label="translations.activeUsers">
        <active-users />
      </ank-tab>
    </ank-tabs>
  </div>
</template>
<script src="./AdminCenterInfo.controller.ts" lang="ts"></script>
<style lang="scss" scoped>
@import "./AdminCenterInfo.scss";
</style>
