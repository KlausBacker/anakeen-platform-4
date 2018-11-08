<template>
    <div class="views-grid-parent">
        <div>
            <kendo-toolbar class="views-grid-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="refreshViews"></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
        <kendo-datasource ref="viewsGrid"
                          :transport-read="getViews"
                          :server-paging="true"
                          :pageable="true"
                          :page-size="20"
                          :schema-data="parseViewsData">
        </kendo-datasource>
        <kendo-grid ref="viewsGridContent" class="views-grid-content"
                    :data-source-ref="'viewsGrid'"
                    :pageable="{ pageSizes: [10,20,40,50]}"
                    :filterable-mode="'row'"
                    :filterable-extra="false"
                    :sortable="true">
            <kendo-grid-column :field="'cvId'" :title="'<b>Logical Name</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'viewId'" :title="'<b>View Name</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'cvStructure'" :title="'<b>Structure</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'maskId'" :title="'<b>Mask</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'order'" :title="'<b>Order</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'viewMode'" :title="'<b>View Mode</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'renderConfigClass'" :title="'<b>Render Class</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'menuList'" :title="'<b>Menu List Label</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'displayed'" :title="'<b>Displayed</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
        </kendo-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./ViewsConf.scss";
</style>
<script src="./ViewsConf.controller.js"></script>