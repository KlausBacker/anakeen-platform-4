<template>
  <div class="timers-plugin">
    <ank-tabs ref="smartTab" v-model="mySelectedTab">
      <ank-tab tab-id="incomingTimerActions" :label="translations.incomingActions">
        <incoming-timer v-bind="$props"></incoming-timer>
      </ank-tab>
      <ank-tab tab-id="previousTimerActions" :label="translations.previousActions">
        <previous-timer v-bind="$props"></previous-timer>
      </ank-tab>
    </ank-tabs>
  </div>
</template>

<style lang="scss">
@import "./Timers.scss";
</style>
<script lang="ts" src="./Timers.ts" />
