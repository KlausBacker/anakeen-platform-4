<template>
  <div class="about-container">
    <div class="about">
      <p class="about-title">{{ $t("AdminCenterInfos.About Anakeen Platform 4") }}</p>

      <div class="about-infos">
        <div class="about-title">
          <img class="about-logo" alt="logo" src="/Images/anakeen-logo.svg"/><span>{{
            $t("AdminCenterInfos.Anakeen Platform 4")
          }}</span>
        </div>
        <div class="about-version">
          <span>
            {{ $t("AdminCenterInfos.Version") }}
          </span>
          : <b>{{ currentVersion }}</b>
        </div>
      </div>
    </div>
  </div>
</template>
<script src="./About.controller.ts" lang="ts"></script>
<style lang="scss" scoped>
@import "./About.scss";
</style>
