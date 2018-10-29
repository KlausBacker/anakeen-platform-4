<template>
    <div class="parameters-parent">
        <div>
            <kendo-toolbar class="parameters-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="refreshParameters"></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
        <kendo-treelist ref="parametersTree"
                        class="parameters-tree"
                        :data-source="remoteDataSource"
                        :height="'100%'"
                        :filterable="true"
                        :resizable="true"
                        :sortable="true"
                        :dataBound="onDataBound"
                        :expand="onExpand"
                        :collapse="onCollapse"
                        :columnMenu="true">
            <kendo-treelist-column :template="columnTemplate('id')" :field="'id'" :title="'Identification'" :width="'10%'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('config')" :field="'config'" :title="'Configuration'" :width="'10%'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('value')" :field="'value'" :title="'Value'"></kendo-treelist-column>
        </kendo-treelist>
    </div>
</template>
<style lang="scss">
    @import "./Parameters.scss";
</style>
<script src="./Parameters.controller.js"></script>
