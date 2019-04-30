<template>
    <div class="vault-manager">
        <ank-splitter ref="vaultSplitter" class="vault-manager-splitter" :panes="panes"
                      localStorageKey="vault-manager-splitter">
            <template slot="left">
                <div class="vault-main-box">
                    <header>
                        <kendo-button class="k-primary" @click="onCreateVault">
                            <i class="fa fa-plus"></i>
                            <span>Create</span>
                        </kendo-button>
                    </header>
                    <div class="vault-grid-box">
                        <div ref="vaultManagerGrid" class="vault-manager-grid"></div>
                    </div>
                </div>
            </template>
            <template slot="right">
                <ank-vault-info :info="info" class="vault-info" @vault-updated="refreshVaultGrid"></ank-vault-info>
            </template>
        </ank-splitter>
        <div ref="createVaultForm" class="vault-manager-form" style="display: none">
            <input ref="newPath" class="k-textbox" placeholder="Server path">
            <div class="vault-resize-inputs">
                <div class="vault-resize-inputs__content">
                    <input ref="newSize" class="k-textbox" placeholder="Logical max size">

                    <kendo-dropdownlist
                            ref="kNewSizeUnit"
                            :data-source="sizeOptions"
                            value="1048576"
                            :data-text-field="'text'"
                            :data-value-field="'value'"
                            :options-label="'Select Size...'">
                    </kendo-dropdownlist>
                </div>
            </div>
            <div class="vault-buttons">
                <kendo-button
                        class="k-primary"
                        @click="requestCreateIt">Create it
                </kendo-button>

                <kendo-button @click="closeWindow">Cancel</kendo-button>
            </div>
        </div>

        <div ref="infoUpdate" class="vault-updated" style="display: none">
            <p>
                <b>{{ requestMessage }}</b>
            </p>
            <kendo-button @click="closeWindow">Close</kendo-button>

        </div>
    </div>
</template>
<style lang="scss">
@import "./VaultManager.scss";
</style>
<script src="./VaultManager.controller.ts" lang="ts">
</script>
