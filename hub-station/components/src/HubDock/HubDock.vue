<template>
  <div
    :class="[
      {
        'hub-dock-component': true,
        'hub-dock--expanded': !collapsed,
        'hub-dock--collapsed': collapsed,
        'hub-dock--animation': animate,
        'hub-dock--collapsable': collapsable,
        'hub-dock--absolute': superposable
      },
      `hub-dock--${position.toLowerCase()}`
    ]"
    :style="dockStyle"
  >
    <div
      ref="dockEl"
      class="hub-dock-content-wrapper"
      :style="dockWrapperStyle"
    >
      <div
        class="hub-dock-transition-wrapper"
        :style="dockWrapperStyle"
      >
        <div
          ref="dockContent"
          class="hub-dock-content-outside"
          @mouseover="onOverDock"
          @mouseleave="onLeaveDock"
        >
          <div class="hub-dock-content">
            <div class="hub-dock-section hub-dock-header">
              <slot name="header" />
            </div>
            <div class="hub-dock-section hub-dock-center">
              <slot />
            </div>
            <div class="hub-dock-section hub-dock-footer">
              <slot name="footer" />
            </div>
          </div>
        </div>
        <div
          v-if="collapsable"
          class="hub-dock-collapse-button"
          @click="toggleDock"
        >
          <i :class="`fa fa-${faCollapseIcon}`" />
        </div>
      </div>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./HubDock.scss";
</style>
<style lang="scss">
@import "./VueBar.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script lang="ts" src="./HubDock.component.ts">
</script>
