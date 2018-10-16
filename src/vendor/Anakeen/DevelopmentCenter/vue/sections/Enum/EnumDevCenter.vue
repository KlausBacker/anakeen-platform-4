<template>
    <div class="enum-grid-parent">
        <div>
            <kendo-toolbar class="enum-grid-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="refreshEnum"></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
        <kendo-datasource ref="enumGrid"
                          :transport-read="getEnum"
                          :server-paging="true"
                          :pageable="true"
                          :page-size="20"
                          :schema-data="parseEnumData"
                          :schema-total="parseEnumTotal"
                          :server-filtering="true">
        </kendo-datasource>
        <kendo-grid ref="enumGridContent" class="enum-grid-content"
                    :data-source-ref="'enumGrid'"
                    :pageable="true"
                    :filterable-mode="'row'"
                    :filterable-extra="false"
                    :sortable="true">
            <kendo-grid-column :field="'name'" :title="'<b>Name</b>'":filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'key'" :title="'<b>Key</b>'"  :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'label'" :title="'<b>Label</b>'"  :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'parentkey'" :title="'<b>Parent</b>'"  :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'disabled'" :title="'<b>Disabled</b>'" :filterable-cell-template="disabledFilter" :filterable-cell-show-operators="false"></kendo-grid-column>
        </kendo-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./EnumDevCenter.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./EnumDevCenter.controller.js"></script>