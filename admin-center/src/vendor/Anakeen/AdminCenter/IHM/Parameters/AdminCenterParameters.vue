<template>
  <div class="parameter-plugin">
    <admin-center-global-parameters
      v-if="globalParameters"
      @switchParameters="switchParameters"
      :switch="globalParameters"
    ></admin-center-global-parameters>
    <admin-center-user-parameters
      v-else
      @switchParameters="switchParameters"
      :switch="globalParameters"
    ></admin-center-user-parameters>
  </div>
</template>

<style>
@import "./AdminCenterParameters.css";
</style>

<script src="./AdminCenterParameters.controller.ts" lang="ts">
</script>
