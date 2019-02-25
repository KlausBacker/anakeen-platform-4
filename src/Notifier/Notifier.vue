<template>
  <div
    class="ank-notifier"
    @ankNotification="publishNotification"
    @ankNotificationClose="closeAllNotification"
  ></div>
</template>

<!-- CSS for notification, should not be scoped because it is not in the component's DOM -->
<style lang="scss">
@import "./Notifier.scss";
</style>

<script src="./Notifier.component.js"></script>
