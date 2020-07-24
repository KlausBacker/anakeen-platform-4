<template>
  <div class="timers-details-incoming-form-wrapper">
    <div class="timers-grid-wrapper-detail" :data-status="infos.donestatus">
      <div ref="gridIncomingTimerDetail">
        <div class="timers-status-incoming-detail">
          <h1>{{ infos.title }}</h1>
          <ul class="testAlogn">
            <li class="timers-attach-date">
              <span>{{ $t("AdminCenterTimersDetails.attachdate") }}</span>
              <span class="timers-headers-values">{{ infos.attachdate }}</span>
            </li>
            <li class="timers-attach-date">
              <span>{{ $t("AdminCenterTimersDetails.tododate") }}</span>
              <span class="timers-headers-values">{{ infos.tododate }}</span>
            </li>
            <li v-if="infos && infos.actions && infos.actions.state" class="timers-workflow">
              <span>{{ $t("AdminCenterTimersDetails.Attached by") }}</span>
              <span class="timers-headers-values">{{ infos.actions.state }}</span>
            </li>
            <li class="timers-status">
              <span>{{ $t("AdminCenterIncomingTranslation.Status") }}</span>
              <span class="timers-headers-values">{{ infos.donestatus }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div ref="smartFormAlert"></div>
  </div>
</template>

<style lang="scss">
@import "./IncomingDetails.scss";
</style>
<script lang="ts" src="./IncomingDetails.ts" />
