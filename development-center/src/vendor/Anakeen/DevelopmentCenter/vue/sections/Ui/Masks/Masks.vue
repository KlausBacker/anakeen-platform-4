<template>
  <ank-splitter ref="masksSplitter" class="masks-splitter" :panes="panes" local-storage-key="ui-masks-splitter">
    <template slot="left">
      <ank-se-grid
        ref="masksGrid"
        :collection="ssName"
        controller="MASKS_GRID_CONTROLLER"
        filterable
        :pageable="{ pageSizes: [100, 200, 500], pageSize: 100 }"
        @rowActionClick="actionClick"
        @dataBound="onGridDataBound"
      >
      </ank-se-grid>
    </template>
    <template slot="right">
      <!--            <router-multi-view :force-multi-views="false" style="display:flex; flex: 1" class="splitter-right"></router-multi-view>-->
      <element-view style="height: 100%" :initid="selectedMask" view-id="!defaultConsultation"></element-view>
    </template>
  </ank-splitter>
</template>
<style lang="scss">
.masks-splitter {
  height: 100%;
  width: 100%;

  .grid-cell--name {
    word-break: break-all;
  }
}

.masks-consult-content {
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
  }
}
</style>
<script src="./Masks.controller.js"></script>
