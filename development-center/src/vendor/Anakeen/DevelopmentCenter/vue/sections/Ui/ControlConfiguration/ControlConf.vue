<template>
  <ank-splitter
    ref="controlSplitter"
    class="control-conf-splitter"
    :panes="panes"
    localStorageKey="ui-control-splitter"
  >
    <template slot="left">
      <ank-se-grid
        ref="controlConfGrid"
        :collection="ssName"
        controller="CONTROL_GRID_CONTROLLER"
        filterable
        defaultExpandable
        :pageable="{ pageSizes: [100, 200, 500], pageSize: 100 }"
        @rowActionClick="actionClick"
        @grid-data-bound="onGridDataBound"
      >
      </ank-se-grid>
    </template>
    <template slot="right">
      <component
        style="height: 100%"
        v-if="selectedControl"
        :is="selectedControl.component"
        v-bind="selectedControl.props"
      ></component>
    </template>
  </ank-splitter>
</template>
<style lang="scss">
.control-conf-splitter {
  height: 100%;
  width: 100%;

  .grid-cell--name {
    word-break: break-all;
  }
}

.control-conf-consult-content {
  width: 100%;
  height: 100%;
}

.control-view-is-opened {
  background: #157efb !important;
  color: white !important;
  .action-label {
    color: white !important;
  }
}

.operator-label {
  display: none;
}
.k-dropdown-operator {
  display: none;
}
.splitter-right {
  width: 100%;
  height: 100%;
  .smart-element-view {
    width: 100%;
    height: 100%;
    overflow: auto;
  }
}
</style>
<script src="./ControlConf.controller.js"></script>
