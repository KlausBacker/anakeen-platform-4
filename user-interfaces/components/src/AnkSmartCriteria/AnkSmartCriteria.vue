<template>
  <div class="smart-criteria-wrapper" @keyup.enter="onEnterKeyupEvent">
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
      <button class="smart-criteria-input-button k-primary" @click="onSubmitButtonClick">{{$t("smartCriteria.Search")}}</button>
    </div>
  </div>
</template>
<style lang="scss">
@import "./AnkSmartCriteria.scss";
</style>
<script src="./AnkSmartCriteria.component.ts" lang="ts"></script>
