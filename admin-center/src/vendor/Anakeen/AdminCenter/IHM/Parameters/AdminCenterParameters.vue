<template>
  <div class="parameter-plugin">
    <ank-tabs ref="smartTab" v-model="mySelectedTab">
      <ank-tab v-if="isGlobalTab" tab-id="globalTab" :label="translations.globalParameters">
        <admin-center-all-parameters
          :selected-tab="mySelectedTab"
          v-bind="$props"
          :user-tab="false"
          @navigate="onNavigate"
          @notify="handleNotification"
        ></admin-center-all-parameters>
      </ank-tab>
      <ank-tab v-if="isUserTab" tab-id="userTab" :label="translations.userParameters">
        <admin-center-all-parameters
          :selected-tab="mySelectedTab"
          v-bind="$props"
          :user-tab="true"
          @navigate="onNavigate"
          @notify="handleNotification"
        ></admin-center-all-parameters>
      </ank-tab>
    </ank-tabs>
  </div>
</template>

<style lang="scss">
@import "./AdminCenterParameters.scss";
</style>

<script src="./AdminCenterParameters.controller.ts" lang="ts"></script>
