<template>
  <div class="ssm-parameters">

    <smart-form
      v-if="haveParameters"
      ref="ssmForm"
      :config="generateSmartForm"
      :options="{ force: true }"
      @beforeSave="onSave"
      @actionClick="onActionClick"
    />
    <div v-else class="no-records">
      <div class="empty-ssm-grid">
        <div class="empty-ssm-grid-icon">
          <i class="material-icons">grid_off</i>
        </div>
        <div class="empty-ssm-grid-text">
          <span>{{ $t("AdminCenterSmartStructure.Smart Structure has no parameters") }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
@import "./SmartStructureManagerParameters.scss";
</style>
<script src="./SmartStructureManagerParameters.controller.ts" lang="ts"></script>
