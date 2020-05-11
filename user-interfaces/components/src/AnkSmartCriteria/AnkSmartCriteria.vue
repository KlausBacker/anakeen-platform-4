<template>
  <div class="smart-criteria-wrapper">
    <div class="smart-criteria-form">
      <ank-smart-form
        ref="smartForm"
        :config="smartFormConfig"
        :options="{ force }"
        @smartElementMounted="initSmartCriteria"
        @smartFieldAfterTabSelect="onSmartCriteriaReady"
        @smartFieldChange="onSmartFieldChange"
        @beforeSave="onBeforeSave"
        @save="onSave"
      />
    </div>
    <div v-if="submit" class="smart-criteria-input">
      <input class="smart-criteria-input-button" type="submit" @click="onSubmitButtonClick" />
    </div>
  </div>
</template>
<style lang="scss">
  @import "./AnkSmartCriteria.scss";
</style>
<script src="./AnkSmartCriteria.component.ts" lang="ts"></script>
