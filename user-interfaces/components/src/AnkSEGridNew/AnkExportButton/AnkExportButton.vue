<template>
  <div ref="exportButtonWrapper" class="grid-export-button" :title="title">
    <div class="grid-export grid-toolbar-action grid-toolbar-export-action" ref="exportButton">
      <ul class="grid-export-action-menu grid-export-menu" ref="actionMenu" v-show="actionMenuVisible">
        <li data-export-menu="root">
          <span role="button" class="k-button k-button-icontext k-grid-export">
            <span :class="iconClass"></span>
            {{ text }}
          </span>
        </li>
      </ul>
      <ul
        class="grid-export-status grid-export-status--error grid-export-menu"
        ref="errorMenu"
        v-show="errorMenuVisible"
      >
        <li data-export-menu="root">
          <span role="button" class="k-button k-button-icontext k-grid-export">
            <span class="grid-export-status-icon k-image material-icons">warning</span>
            <span class="grid-export-status-text" ref="errorMenuText">Error</span>
          </span>
        </li>
      </ul>
      <span
        role="button"
        class="k-button k-button-icontext grid-export-status grid-export-status--success"
        ref="successMenu"
        v-show="successMenuVisible"
      >
        <span class="material-icons k-image grid-export-status-icon">check</span>
        <span class="grid-export-status-text" ref="successMenuText">Upload done</span>
      </span>
      <ul
        class="grid-export-status grid-export-status--pending grid-export-menu"
        ref="pendingMenu"
        v-show="pendingMenuVisible"
      >
        <li data-export-menu="root">
          <span role="button" class="k-button k-button-icontext k-grid-export">
            <span class="material-icons k-image grid-export-status-icon">cached</span>
            <div class="grid-export-status-progress-bar-wrapper">
              <div class="grid-export-status-progress-bar"></div>
              <span class="grid-export-status-progress-bar-text">
                <span class="exported"></span> / <span class="total"></span>
              </span>
            </div>
          </span>
        </li>
      </ul>
    </div>
  </div>
</template>
<style lang="scss">
@import "./AnkExportButton.scss";
</style>
<script src="./AnkExportButton.controller.ts" lang="ts"></script>
