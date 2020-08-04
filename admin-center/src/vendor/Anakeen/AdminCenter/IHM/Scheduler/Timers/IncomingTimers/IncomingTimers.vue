<template>
  <div class="incoming-timers-wrapper">
    <ank-split-panes
      ref="splitter"
      watch-slots
      vertical collapse-buttons
      class="timers-splitter"
      localStorageKey="admin-center-timers-splitter"
    >
      <div class="timers-grid-wrapper">
        <div ref="gridIncomingTimer"></div>
      </div>
      <div class="timers-form-wrapper">
        <div v-if="isEmpty" class="timers-empty">
          <span class="material-icons">schedule</span>
          <p>{{ $t("AdminCenterTimers.Select an Timer") }}</p>
        </div>
        <div v-if="!isEmpty" class="timers-grid-wrapper-detail">
          <details-incoming-timer :uri-timer="uriTimer"></details-incoming-timer>
        </div>
        <div ref="smartFormAlert"></div>
      </div>
    </ank-split-panes>
  </div>
</template>

<style lang="scss">
@import "./IncomingTimers.scss";
</style>
<script lang="ts" src="./IncomingTimers.ts" />
