<template>
  <div class="scheduler-plugin">
    <ank-tabs ref="smartTab" v-model="SelectedSchedulerTab">
      <ank-tab tab-id="TimersTab" :label="translations.timersTab">
        <admin-center-timers :timer-tab="true"></admin-center-timers>
      </ank-tab>
      <ank-tab tab-id="TasksTab" :label="translations.tasksTab">
        <admin-center-tasks :timer-tab="false"></admin-center-tasks>
      </ank-tab>
    </ank-tabs>
  </div>
</template>
<style scoped lang="scss">
@import "./AdminCenterScheduler.scss";
</style>
<script src="./AdminCenterScheduler.controller.ts" lang="ts"></script>
