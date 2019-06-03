<template>
    <div class="security-roles-section">
        <ank-splitter class="roles-splitter" ref="rolesSplitter" :panes="panes" localStorageKey="security-roles-splitter">
            <template slot="left">
                <ank-se-grid
                        ref="roleContent"
                        class="role-content"
                        collection="ROLE"
                        :pageSizes="[100,200,500]"
                        @action-click="selectRole"
                        @grid-ready="setGridOption"
                        @grid-error="onGridError">
                    <ank-se-grid-column title="Identification" field="id" hidden="true"></ank-se-grid-column>
                    <ank-se-grid-column title="Title" field="title"></ank-se-grid-column>
                    <ank-se-grid-column title="Reference (login)" field="role_login"></ank-se-grid-column>
                    <ank-se-grid-column title="Logical name" field="name"></ank-se-grid-column>
                    <ank-se-grid-actions>
                        <ank-se-grid-action action="consultRole" title="consult"></ank-se-grid-action>
                    </ank-se-grid-actions>
                </ank-se-grid>
            </template>
            <template slot="right">
<!--                <router-multi-view :force-multi-views="false" style="display:flex; flex: 1; height: 100%;"></router-multi-view>-->
            </template>
        </ank-splitter>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./RoleDevCenter.scss";
</style>
<script src="./RoleDevCenter.controller.js"></script>