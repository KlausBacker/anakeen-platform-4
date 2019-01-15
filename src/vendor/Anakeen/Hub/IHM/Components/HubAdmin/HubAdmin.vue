<template>
    <div>
        <div class="hub-admin-parent">
            <div class="hub-admin-header">
                <div class="hub-admin-header__content">
                    <span>Hub Admin</span>
                </div>
                <div class="hub-admin-header__content">
                    <identity :large="true" :email-alterable="true" :password-alterable="true"></identity>
                    <logout></logout>
                </div>
            </div>
        </div>
        <div>
            <ank-splitter ref="hubAdminSplitter" class="hub-admin-splitter" :panes="panes"
                          localStorageKey="hub-admin-splitter">
                <template slot="left">
                    <div class="hub-admin-content">
                        <div class="hub-admin-grid">
                            <grid collection="HUBCONFIGURATION"
                                  :reorderable="true"
                                  class="hub-admin"
                                  ref="hubGrid"
                                  @grid-ready="toolbarConfig"
                                  @action-click="actionClick"
                                  @toolbar-action-click="toolbarActionClick"
                                  :contextTitles="false">
                                <grid-column title="Element position" field="hub_docker_position" :width="'10rem'"></grid-column>
                                <grid-column title="Order" field="hub_order" :width="'6rem'"></grid-column>
                                <grid-column title="Icon" field="hub_final_icon" :width="'10rem'" :encoded="'false'"></grid-column>
                                <grid-column title="Title" field="title"></grid-column>
                                <grid-actions>
                                    <grid-action action="consult" iconClass="k-icon k-i-folder-open"></grid-action>
                                    <grid-action action="edit"></grid-action>
                                </grid-actions>
                                <grid-toolbar>
                                    <grid-toolbar-action action="config" title="Configuration" iconClass="k-icon k-i-gear"></grid-toolbar-action>
                                    <grid-toolbar-action action="create"></grid-toolbar-action>
                                </grid-toolbar>
                            </grid>
                        </div>
                    </div>
                </template>
                <template slot="right">
                    <smartElem ref="smartConfig" class="hub-modal"></smartElem>
                </template>
            </ank-splitter>
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
