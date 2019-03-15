<template>
  <div
    :class="[
      {
        'hub-dock-component': true,
        'hub-dock--expanded': !collapsed,
        'hub-dock--collapsed': collapsed,
        'hub-dock--collapsable': collapsable,
        'hub-dock--absolute': superposable
      },
      `hub-dock--${position.toLowerCase()}`
    ]"
    :style="dockStyle"
  >
    <div
      class="hub-dock-content-wrapper"
      ref="dockEl"
      :style="dockWrapperStyle"
    >
      <div class="hub-dock-transition-wrapper" :style="dockWrapperStyle">
        <div
          class="hub-dock-content"
          @mouseover="onOverDock"
          @mouseleave="onLeaveDock"
        >
          <div class="hub-dock-section hub-dock-header">
            <slot name="header"></slot>
          </div>
          <div class="hub-dock-section hub-dock-center">
            <slot></slot>
          </div>
          <div class="hub-dock-section hub-dock-footer">
            <slot name="footer"></slot>
          </div>
        </div>
        <div
          v-if="expandable"
          class="hub-dock-collapse-button"
          @click="toggleDock"
        >
          <i :class="`fa fa-${faCollapseIcon}`"></i>
        </div>
      </div>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./HubDock.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./HubDock.component.ts"></script>
