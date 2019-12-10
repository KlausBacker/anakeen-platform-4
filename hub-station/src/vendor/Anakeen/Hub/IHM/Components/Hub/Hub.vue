<template>
  <div class="hub-station-main">
    <hub-station
      :config="config"
      @hubNotify="onNotify"
    />
    <ank-notifier
      ref="ankNotifier"
      position="top-right"
      default-type="notice"
    />
  </div>
</template>

<!-- CSS to this component only -->
<style lang="scss">
@import "./Hub.scss";
</style>
<!-- Global CSS -->
<script src="./Hub.controller.js"></script>
