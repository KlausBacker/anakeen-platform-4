<template>
    <div class="routes-acl-content">
        <div>
            <kendo-toolbar class="routes-grid-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="refreshRoutes"></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
        <kendo-datasource ref="routesGrid"
                          :transport-read="getRoutes"
                          :server-paging="true"
                          :pageable="true"
                          :page-size="20"
                          :schema-data="parseRoutesData"
                          :server-filtering="true">
        </kendo-datasource>
        <kendo-grid ref="routesGridContent" class="routes-grid-content"
                    :data-source-ref="'routesGrid'"
                    :pageable="true"
                    :filterable-mode="'row'"
                    :filterable-extra="false"
                    :sortable="true">
            <kendo-grid-column :field="'nameSpace'" :title="'<b>Namespace</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'name'" :title="'<b>Name</b>'" :property="true" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'method'" :title="'<b>Method</b>'" :property="true" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'pattern'" :title="'<b>Pattern</b>'" :property="true" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'requiredAccess'" :title="'<b>Required Accesses</b>'" :property="true" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :template="displayMultiple" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'decscription'" :title="'<b>Description</b>'" :property="true" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
        </kendo-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./RoutesAcl.scss";
</style>
<script src="./RoutesAcl.controller.js"></script>