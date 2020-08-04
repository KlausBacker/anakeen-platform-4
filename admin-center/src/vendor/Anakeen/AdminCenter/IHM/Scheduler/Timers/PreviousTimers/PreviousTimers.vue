<template>
  <div class="previous-timers-wrapper">
    <ank-split-panes
      ref="splitter"
      watch-slots
      vertical collapse-buttons
      class="previous-timers-splitter"
      localStorageKey="admin-center-timers-splitter"
    >
      <div class="previous-timers-grid-wrapper">
        <div ref="gridPreviousTimer"></div>
      </div>
      <div class="previous-timers-form-wrapper">
        <div v-if="isEmpty" class="previous-timers-empty">
          <span class="material-icons">schedule</span>
          <p>{{ $t("AdminCenterTimers.Select an Timer") }}</p>
        </div>
        <div v-if="!isEmpty" class="timers-grid-wrapper-detail">
          <details-previous-timer :uri-timer="uriTimer"></details-previous-timer>
        </div>
        <div ref="smartFormAlert"></div>
      </div>
    </ank-split-panes>
  </div>
</template>

<style lang="scss">
@import "./PreviousTimers.scss";
</style>
<script lang="ts" src="./PreviousTimers.ts" />
