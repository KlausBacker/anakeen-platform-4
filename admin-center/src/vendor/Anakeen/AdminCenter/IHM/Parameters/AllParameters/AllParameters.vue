<template>
  <div class="param-wrapper">
    <ank-split-panes
      ref="splitter"
      watch-slots
      vertical collapse-buttons
      class="param-splitter"
      localStorageKey="admin-center-param-splitter"
    >
      <div class="param-grid-wrapper">
        <div class="user-search-smart-form">
          <ank-smart-form
            v-if="userTab"
            ref="userSmartForm"
            class="user-param-smart-form"
            :config="selectUserForm"
            :options="{ withCloseConfirmation: false }"
            @smartFieldChange="userChange"
            @ready="loadParamFromVu"
          />
        </div>

        <div ref="gridWrapper"></div>
      </div>
      <div class="param-form-wrapper">
        <div v-if="!selectedParam" class="param-empty">
          <div class="param-empty">
            <span class="material-icons">format_list_number</span>
            <p>{{ $t("AdminCenterAllParameter.Select parametrer") }}</p>
          </div>
        </div>
        <div v-else class="else-user-smart-form-parameter">
          <ank-smart-form
            v-if="selectedParam"
            ref="formParameters"
            class="user-smart-form-parameter"
            :config="smartFormData"
            :options="{ withCloseConfirmation: false }"
            @smartFieldChange="updateModifications"
            @actionClick="menuClick"
          ></ank-smart-form>
        </div>
        <div ref="smartFormAlert"></div>
      </div>
    </ank-split-panes>
  </div>
</template>
<style lang="scss">
@import "./AllParameters.scss";
</style>
<script lang="ts" src="./AllParameters.ts" />
