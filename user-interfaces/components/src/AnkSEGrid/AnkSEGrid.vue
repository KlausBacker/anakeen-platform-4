<template>
  <div ref="gridWrapper" class="smart-element-grid">
    <div class="grid-widget-wrapper">
      <div class="grid-widget-header">
        <slot name="gridHeader" v-bind:gridComponent="gridInstance"></slot>
      </div>
      <div class="grid-widget" ref="kendoGrid"></div>
      <div class="grid-widget-footer">
        <slot name="gridFooter" v-bind:gridComponent="gridInstance">
          <div class="grid-default-footer">
            <ank-se-grid-pager :gridComponent="gridInstance"></ank-se-grid-pager>
            <ank-se-grid-expand-button :gridComponent="gridInstance"></ank-se-grid-expand-button>
          </div>
        </slot>
      </div>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
@import "./AnkSEGrid.scss";
</style>

<script src="./AnkSEGrid.controller.ts" lang="ts"></script>
