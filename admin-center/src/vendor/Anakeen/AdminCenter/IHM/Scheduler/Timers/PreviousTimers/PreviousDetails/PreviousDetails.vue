<template>
  <div class="timers-previous-form-wrapper">
    <div class="timers-grid-wrapper-detail" :data-status="info.donestatus">
      <div ref="gridPreviousTimerDetail">
        <div class="timers-status-incoming-detail">
          <h1>{{ info.title }}</h1>
          <ul>
            <li class="timers-status">
              <span>{{ $t("AdminCenterTimersDetails.donestatus") }}</span>
              <span class="timers-headers-values">{{ info.donestatus }}</span>
            </li>
            <li class="timers-attach-date">
              <span>{{ $t("AdminCenterTimersDetails.formatAttachDate") }}</span>
              <span class="timers-headers-values">{{ info.formatAttachDate }}</span>
            </li>
            <li v-if="info && info.actions && info.actions.state" class="timers-workflow">
              <span>{{ $t("AdminCenterTimersDetails.Attached by") }}</span>
              <span class="timers-headers-values">{{ info.actions.state }}</span>
            </li>
            <li v-if="info.result" class="timers-result-incoming-detail">{{ info.result }}</li>
          </ul>
        </div>
      </div>
    </div>
    <div ref="smartFormAlert"></div>
  </div>
</template>

<style lang="scss">
@import "./PreviousDetails.scss";
</style>
<script lang="ts" src="./PreviousDetails.ts" />
