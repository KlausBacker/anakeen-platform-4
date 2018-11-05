<template>
    <div class="defaults-values-parent">
        <div>
            <kendo-toolbar class="defaults-values-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="refreshDefaultValues"></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
        <kendo-treelist ref="defaultValuesTree"
                        class="defaults-values-tree"
                        :data-source="valuesDataSource"
                        :height="'100%'"
                        :filterable="{ extra: false}"
                        :resizable="true"
                        :sortable="true"
                        :dataBound="onDataBound"
                        :expand="onExpand"
                        :collapse="onCollapse"
                        :columnMenu="true">
            <kendo-treelist-column :field="'idVal'" :title="'Identification'" :min-width="'10%'"></kendo-treelist-column>
            <kendo-treelist-column :field="'config'" :title="'Configuration'" ></kendo-treelist-column>
            <kendo-treelist-column :field="'type'" :title="'Type'" ></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('value')" :field="'value'" :title="'Value'"></kendo-treelist-column>
        </kendo-treelist>
    </div>
</template>
<style lang="scss">
    @import "./DefaultsValues.scss";
</style>
<script src="./DefaultsValues.controller.js"></script>