<template>
    <div class="hub-admin-parent">
        <header class="hub-admin-header">
            <div class="hub-admin-header__content">
                <span>Hub Admin</span>
            </div>
            <div class="hub-admin-header__content">
                <identity :large="true" :email-alterable="true" :password-alterable="true"></identity>
                <logout></logout>
            </div>
        </header>
        <div class="hub-admin-content">
            <div class="hub-admin-grid">
                <grid collection="HUBCONFIGURATION"
                      :checkable="true"
                      :reorderable="true"
                      class="hub-admin"
                      ref="hubGrid"
                      @grid-ready="toolbarConfig">
                    <grid-column title="Icon" field="icon"></grid-column>
                    <grid-column title="Title" field="title"></grid-column>
                    <grid-column title="Smart field title" field="hub_title"></grid-column>
                    <grid-column title="Language" field="hub_language"></grid-column>
                    <grid-actions>
                        <grid-action action="consult"></grid-action>
                        <grid-action action="edit"></grid-action>
                    </grid-actions>
                    <grid-toolbar>
                        <grid-toolbar-action action="columns" title="Columns configuration"></grid-toolbar-action>
                        <grid-toolbar-action action="export" title="Export"></grid-toolbar-action>
                    </grid-toolbar>
                </grid>
                <modal name="hubConfigModal" :width="'80%'" :height="'80%'" @opened="openedModal">
                    <smartElem ref="smartConfig" class="hub-modal"></smartElem>
                </modal>
            </div>
        </div>
    </div>
</template>

<!-- CSS to this component only -->
<style lang="scss">
    @import "HubAdmin";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./HubAdmin.controller.js"></script>
