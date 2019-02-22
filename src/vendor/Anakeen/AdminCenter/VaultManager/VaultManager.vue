<template>
    <div class="vault-manager">
        <ank-splitter ref="vaultSplitter" class="vault-manager-splitter" :panes="panes"
                      localStorageKey="vault-manager-splitter">
            <template slot="left">
                <div class="vault-manager-grid"></div>
            </template>
            <template slot="right">
                <ank-vault-info :info="info" class="vault-info"></ank-vault-info>
            </template>
        </ank-splitter>
    </div>
</template>
<style>
    @import "./VaultManager.scss";
</style>
<script src="./VaultManager.controller.ts" lang="ts"></script>
