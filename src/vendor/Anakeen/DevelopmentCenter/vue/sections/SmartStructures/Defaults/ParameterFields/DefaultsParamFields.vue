<template>
    <div class="defaults-param-parent">
        <div>
            <kendo-toolbar class="defaults-param-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="refreshDefaults"></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
        <kendo-treelist ref="defaultsTree"
                        class="defaults-param-tree"
                        :data-source="remoteDataSource"
                        :height="'100%'"
                        :filterable="true"
                        :resizable="true"
                        :sortable="true"
                        :dataBound="onDataBound"
                        :expand="onExpand"
                        :collapse="onCollapse"
                        :columnMenu="true"
                        :messages="{ noRows: 'There are no data for this Smart Structure...' }">
            <kendo-treelist-column :template="columnTemplate('id')" :field="'id'" :title="'Identification'" :width="'10%'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('type')" :field="'type'" :title="'Type'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('labeltext')" :field="'labeltext'" :title="'Label'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('config')" :field="'config'" :title="'Configuration'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('value')" :field="'value'" :title="'Value'"></kendo-treelist-column>
        </kendo-treelist>
    </div>
</template>
<style lang="scss">
    @import "./DefaultsParamFields.scss";
</style>
<script src="./DefaultsParamFields.controller.js"></script>
