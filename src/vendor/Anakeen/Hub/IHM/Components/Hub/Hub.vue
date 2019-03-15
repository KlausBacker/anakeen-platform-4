<template>
  <div class="hub-station-main">
    <hub-station :config="config" @hubNotify="onNotify"></hub-station>
    <ank-notifier
      ref="ankNotifier"
      position="top-right"
      defaultType="notice"
    ></ank-notifier>
  </div>
</template>

<!-- CSS to this component only -->
<style lang="scss">
@import "./Hub.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./Hub.controller.js"></script>
