<template>
    <div class="views-grid-parent">
        <kendo-datasource ref="viewsGrid"
                          :transport-read="getViews"
                          :schema-data="parseViewsData"
                          :sort="[{ field: 'cvId', dir: 'asc'},{field: 'viewMode', dir:'asc'},{field: 'order', dir:'asc'}]">
        </kendo-datasource>
        <kendo-grid ref="viewsGridContent" class="views-grid-content"
                    :data-source-ref="'viewsGrid'"
                    :filterable-mode="'row'"
                    :filterable-extra="false"
                    :sortable="false"
                    @databound="onGridDataBound"
                    :noRecords="{ template: `<div class='empty-grid'>
                                                <div class='empty-grid-icon'>
                                                   <i class='material-icons'>grid_off</i>
                                                </div>
                                                <div class='empty-grid-text'>
                                                    <span> No View Controller data to display for this Smart Structure</span>
                                                </div>
                                            </div>`}">
            <kendo-grid-column :field="'cvId'" :title="'<b>Logical Name</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" :template="displayLink('cvId')"></kendo-grid-column>
            <kendo-grid-column :field="'cvStructure'" :title="'<b>Structure</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" :template="displayLink('cvStructure')"></kendo-grid-column>
            <kendo-grid-column :field="'viewId'" :title="'<b>View Name</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'viewLabel'" :title="'<b>View Label</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'maskId'" :title="'<b>Mask</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" :template="displayLink('maskId')"></kendo-grid-column>
            <kendo-grid-column :field="'order'" :title="'<b>Order</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" :width="'4rem'"></kendo-grid-column>
            <kendo-grid-column :field="'viewMode'" :title="'<b>View Mode</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" :width="'5rem'"></kendo-grid-column>
            <kendo-grid-column :field="'renderConfigClass'" :title="'<b>Render Class</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'menuList'" :title="'<b>Menu List Label</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'displayed'" :title="'<b>Displayed</b>'" :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol" :width="'5rem'" :encoded="false"></kendo-grid-column>
        </kendo-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./ViewsConf.scss";
</style>
<script src="./ViewsConf.controller.js"></script>