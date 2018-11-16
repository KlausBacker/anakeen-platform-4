<template>
    <div class="permissions-grid-parent">
        <kendo-datasource ref="permissionsGrid"
                          :transport-read="getPermissions"
                          :server-paging="true"
                          :pageable="true"
                          :page-size="20"
                          :schema-data="parsePermissionsData"
                          :server-filtering="true">
        </kendo-datasource>
        <kendo-grid ref="permissionsGridContent" class="permissions-grid-content"
                    :data-source-ref="'permissionsGrid'"
                    :pageable="{ pageSizes: [10,20,40,50]}"
                    :filterable-mode="'row'"
                    :filterable-extra="false"
                    :sortable="true">
            <kendo-grid-column :field="'id'" :title="'<b>Transition</b>'":filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'mail'" :title="'<b>Mail Template</b>'"  :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'timers.volatile'" :title="'<b>Volatile Timers</b>'"  :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'timers.persistent'" :title="'<b>Persistent Timers</b>'" :filterable-cell-template="disabledFilter" :filterable-cell-show-operators="false"></kendo-grid-column>
            <kendo-grid-column :field="'timers.toUnattach'" :title="'<b>Timers to unattach</b>'" :filterable-cell-template="disabledFilter" :filterable-cell-show-operators="false"></kendo-grid-column>
        </kendo-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Permissions.scss";
</style>
<script src="./Permissions.controller.js"></script>