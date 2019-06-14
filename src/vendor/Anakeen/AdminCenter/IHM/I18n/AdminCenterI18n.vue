<template>
    <div class="i18n-parent">
        <div class="i18nManager-toolbar">
            <kendo-toolbar ref="i18nToolbarLocale" class="i18n-toolbar-locale" @toggle="changeLocale">
                <kendo-toolbar-item type="buttonGroup" :buttons="[
                {  id: 'i18n-locale-button-en', togglable: true, group: 'text-align', imageUrl: '/api/v2/images/assets/sizes/20x20/Helppage/flags/us.png'},
                {  id: 'i18n-locale-button-fr', selected: true, togglable: true, group: 'text-align', imageUrl: '/api/v2/images/assets/sizes/20x20/Helppage/flags/fr.png'},
               ]">
                </kendo-toolbar-item>
            </kendo-toolbar>
            <kendo-toolbar ref="i18nToolbarFiles" class="i18n-toolbar-files">
                <kendo-toolbar-item
                        type="button"
                        icon="download"
                        text="Export file"
                        @click="exportLocaleFile"
                ></kendo-toolbar-item>
                <kendo-toolbar-item
                        type="button"
                        icon="upload"
                        text="Import file"
                        @click="importLocaleFile"
                ></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
        <div class="i18n-grid-parent">
            <div ref="i18nGrid" class="i18n-grid"></div>
        </div>
    </div>
</template>
<script src="./AdminCenterI18n.controller.ts" lang="ts">
</script>
<style lang="scss" scoped>
    @import "./AdminCenterI18n.scss";
</style>