<template>
    <div class="defaults-fields-parent">
        <div>
            <kendo-toolbar class="defaults-fields-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="refreshDefaultFields"></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
        <kendo-treelist ref="defaultFieldsTree"
                        class="defaults-fields-tree"
                        :data-source="remoteDataSource"
                        :height="'100%'"
                        :filterable="{ extra: false}"
                        :resizable="true"
                        :sortable="true"
                        :dataBound="onDataBound"
                        :expand="onExpand"
                        :collapse="onCollapse"
                        :columnMenu="true">
            <kendo-treelist-column :template="columnTemplate('id')" :field="'id'" :title="'Identification'" :width="'10%'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('type')" :field="'type'" :title="'Type'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('labeltext')" :field="'labeltext'" :title="'Label'"></kendo-treelist-column>
            <kendo-treelist-column :field="'config'" :title="'Configuration'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('value')" :field="'value'" :title="'Value'"></kendo-treelist-column>
        </kendo-treelist>
    </div>
</template>
<style lang="scss">
    @import "./DefaultsFields.scss";
</style>
<script src="./DefaultsFields.controller.js"></script>
