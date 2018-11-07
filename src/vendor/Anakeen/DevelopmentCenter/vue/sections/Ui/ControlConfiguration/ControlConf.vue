<template>
    <div class="access-grid-parent">
        <div class="access-grid">
            <div>
                <kendo-toolbar class="access-grid-toolbar">
                    <kendo-toolbar-item type="button" icon="refresh" @click="refreshAccessConf"></kendo-toolbar-item>
                </kendo-toolbar>
            </div>
            <kendo-datasource ref="accessGrid"
                              :transport-read="getAccessConf"
                              :server-paging="true"
                              :pageable="true"
                              :page-size="20"
                              :schema-data="parseAccessConfData"
                              :schema-total="parseAccessConfTotal"
                              :server-filtering="true">
            </kendo-datasource>
            <kendo-grid ref="accessGridContent" class="access-grid-content"
                        :data-source-ref="'accessGrid'"
                        :pageable="{ pageSizes: [10,20,40,50]}"
                        :filterable-mode="'row'"
                        :filterable-extra="false"
                        :sortable="true">
                <kendo-grid-column :field="'name'" :title="'<b>Name</b>'" :filterable-cell-operator="'contains'"
                                   :filterable-cell-show-operators="false"
                                   :filterable-cell-template="autoFilterCol"></kendo-grid-column>
                <kendo-grid-column :field="'renderAccess'" :title="'<b>Render Access</b>'"
                                   :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false"
                                   :filterable-cell-template="autoFilterCol"></kendo-grid-column>
                <kendo-grid-column :field="'primaryMask'" :title="'<b>Primary Mask</b>'"
                                   :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false"
                                   :filterable-cell-template="autoFilterCol"></kendo-grid-column>
                <kendo-grid-column :template="displayConsult" :title="'&nbsp'" :width="100"></kendo-grid-column>
            </kendo-grid>
        </div>
        <div class="access-document">
            <div class="access-document-content">
                <ank-smart-element ref="accessRenderConsult"></ank-smart-element>
            </div>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./ControlConf.scss";
</style>
<script src="./ControlConf.controller.js"></script>
