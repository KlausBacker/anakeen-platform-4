<template>
    <div class="routes-permissions-content">
        <div>
            <kendo-toolbar class="routes-permissions-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="refreshPermissions"></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
        <kendo-datasource ref="routesPermissions"
                          :transport-read="getPermissions"
                          :server-paging="true"
                          :pageable="true"
                          :page-size="20"
                          :schema-data="parsePermissionsData"
                          :schema-total="parsePermissionsTotal">
        </kendo-datasource>
        <kendo-grid ref="routesPermissionsContent"
                    class="routes-grid-content"
                    :data-source-ref="'routesPermissions'"
                    :pageable="true"
                    :filterable-mode="'row'"
                    :filterable-extra="false"
                    :sortable="true">
            <kendo-grid-column :field="'accessNs'" :title="'<b>Namespace</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'accessName'" :title="'<b>Name</b>'" :template="displayLink" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'account.reference'" :title="'<b>Account</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
        </kendo-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "RoutesPermissions.scss";
</style>
<script src="./RoutesPermissions.controller.js"></script>