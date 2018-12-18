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
                                  :checkable="true"
                                  :reorderable="true"
                                  class="hub-admin"
                                  ref="hubGrid"
                                  @grid-ready="toolbarConfig"
                                  @action-click="actionClick">
                                <grid-column title="Icon" field="icon"></grid-column>
                                <grid-column title="Title" field="title"></grid-column>
                                <grid-column title="Smart field title" field="hub_title"></grid-column>
                                <grid-column title="Language" field="hub_language"></grid-column>
                                <grid-actions>
                                    <grid-action action="consult" iconClass="k-icon k-i-folder-open"></grid-action>
                                    <grid-action action="edit"></grid-action>
                                </grid-actions>
                                <grid-toolbar>
                                    <grid-toolbar-action action="export" title="Export"></grid-toolbar-action>
                                </grid-toolbar>
                            </grid>
                        </div>
                    </div>
                </template>
                <template slot="right">
                    <smartElem ref="smartConfig" class="hub-modal"></smartElem> <!-- TO DO : switch to router multiview config when router is merged and add index js config -->
                    <!--<router-multi-view :force-multi-views="false" style="display:flex; flex: 1" class="splitter-right"></router-multi-view>-->
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
