<template>
  <div class="vault-manager">
    <ank-split-panes
      watch-slots
      vertical
      ref="vaultSplitter"
      class="vault-manager-splitter"
      localStorageKey="vault-manager-splitter"
    >
      <div class="vault-main-box" splitpanes-size="30">
        <header>
          <kendo-button class="k-primary" @click="onCreateVault">
            <i class="fa fa-plus"></i>
            <span>{{ $t("AdminCenterVaultManager.Create") }}</span>
          </kendo-button>
        </header>
        <div class="vault-grid-box">
          <div ref="vaultManagerGrid" class="vault-manager-grid"></div>
        </div>
      </div>

      <ank-vault-info :info="info" class="vault-info" @vault-updated="refreshVaultGrid" splitpanes-size="70"></ank-vault-info>
    </ank-split-panes>
    <div ref="createVaultForm" class="vault-manager-form" style="display: none">
      <input ref="newPath" class="k-textbox" :placeholder="translations.ServerPath" />
      <div class="vault-resize-inputs">
        <div class="vault-resize-inputs__content">
          <input ref="newSize" class="k-textbox" :placeholder="translations.LogicalMaxSize" />

          <kendo-dropdownlist
            ref="kNewSizeUnit"
            :data-source="sizeOptions"
            value="1048576"
            :data-text-field="'text'"
            :data-value-field="'value'"
            :options-label="'Select Size...'"
          >
          </kendo-dropdownlist>
        </div>
      </div>
      <div class="vault-buttons">
        <kendo-button class="k-primary" @click="requestCreateIt">{{
          $t("AdminCenterVaultManager.Create it")
        }}</kendo-button>

        <kendo-button @click="closeWindow">{{ $t("AdminCenterVaultManager.Cancel") }}</kendo-button>
      </div>
    </div>

    <div ref="infoUpdate" class="vault-updated" style="display: none">
      <p>
        <b>{{ requestMessage }}</b>
      </p>
      <kendo-button @click="closeWindow">{{ $t("AdminCenterVaultManager.Close") }}</kendo-button>
    </div>
  </div>
</template>
<style lang="scss">
@import "./VaultManager.scss";
</style>
<script src="./VaultManager.controller.ts" lang="ts"></script>
