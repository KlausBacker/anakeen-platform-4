<template>
  <div class="enum-wrapper">
    <ank-split-panes
      watch-slots
      vertical collapse-buttons
      ref="splitter"
      class="enum-splitter"
      localStorageKey="admin-center-enum-splitter"
    >
      <div class="enum-grid-wrapper">
        <div ref="gridWrapper"></div>
      </div>
      <div class="enum-form-wrapper" >
        <div v-show="!selectedEnum" class="enum-empty">
          <span class="material-icons">format_list_number</span>
          <p>{{ $t("AdminCenterEnum.Select enumerate") }}</p>
        </div>
        <ank-smart-form v-show="selectedEnum" class="enum-smart-form" ref="smartForm" :config="smartFormData" @ready="smartFormReady" @smartFieldArrayChange="manageRows" @smartFieldChange="updateModifications" @actionClick="saveModifications"></ank-smart-form>
        <div ref="smartFormAlert"></div>
      </div>
    </ank-split-panes>
  </div>
</template>
<style lang="scss">
@import "./AdminCenterEnum.scss";
</style>
<script src="./adminCenterEnum.controller.ts" lang="ts"></script>
