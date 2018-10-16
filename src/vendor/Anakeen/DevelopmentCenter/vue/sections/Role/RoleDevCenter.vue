<template>
    <div class="role-parent">
        <ank-se-grid
                     ref="roleContent"
                     class="role-content"
                     collection="ROLE"
                     :reorderable="true"
                     @action-click="selectRole"
                     @grid-ready="setGridOption"
                     @before-grid-cell-render="setCellRender">
            <ank-se-grid-column title="Icon" field="icon" :hidden="true"></ank-se-grid-column>
            <ank-se-grid-column title="Title" field="title"></ank-se-grid-column>
            <ank-se-grid-column title="Reference (login)" field="role_login"></ank-se-grid-column>
            <ank-se-grid-column title="Logical name" field="name"></ank-se-grid-column>
            <ank-se-grid-actions>
                <ank-se-grid-action action="consultRole" title="consult"></ank-se-grid-action>
            </ank-se-grid-actions>
        </ank-se-grid>
        <modal name="roleModal" :width="'80%'" :height="'80%'" @opened="openedModal">
            <ank-smart-element ref="roleConsult" class="role-modal"></ank-smart-element>
        </modal>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./RoleDevCenter.scss";
</style>
<script src="./RoleDevCenter.controller.js"></script>