<template>
    <div>
        <div class="hub-admin-parent">
            <div class="hub-admin-header">
                <div class="hub-admin-header__content">
                    <span>Hub Station Admin</span>
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
                            <grid
                                    ref="hubGrid"
                                    :urlConfig="`/api/v2/hub/station/${hubId}/admin/config/`"
                                    :pageSizes="[100,200,500]"
                                    :sortable="''"
                                    class="hub-admin"
                                    @grid-ready="toolbarConfig"
                                    @action-click="actionClick"
                                    @toolbar-action-click="toolbarActionClick"
                                    :contextTitles="false">
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
