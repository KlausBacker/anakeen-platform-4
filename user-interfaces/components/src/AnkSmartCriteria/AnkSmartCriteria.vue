<template>
  <div class="smart-criteria-wrapper" @keyup.enter="onEnterKeyupEvent">
    <slot v-if="loading">
      <div class="smart-criteria-loading-screen">
        <div class="dcpLoading">
          <ank-loading></ank-loading>
        </div>
      </div>
    </slot>
    <div v-show="!loading" class="smart-criteria-form">
      <ank-smart-form
        ref="smartForm"
        :config="smartFormConfig"
        :options="{ withCloseConfirmation: false }"
        @smartElementMounted="initSmartCriteria"
        @smartFieldAfterTabSelect="onSmartCriteriaReady"
        @smartFieldChange="onSmartFieldChange"
        @beforeSave="onBeforeSave"
        @save="onSave"
        @smartFieldHelperSearch="onSmartFieldHelperSearch"
      />
    </div>
    <div v-if="submit" v-show="!loading" class="smart-criteria-input">
      <button class="smart-criteria-input-button k-primary" @click="onSubmitButtonClick">{{$t("smartCriteria.Search")}}</button>
    </div>
  </div>
</template>
<style lang="scss">
@import "./AnkSmartCriteria.scss";
</style>
<script src="./AnkSmartCriteria.component.ts" lang="ts"></script>
