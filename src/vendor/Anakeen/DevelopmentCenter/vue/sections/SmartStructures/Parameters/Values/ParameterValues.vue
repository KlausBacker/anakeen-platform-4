<template>
    <div class="parameters-values-parent">
        <div>
            <kendo-toolbar class="parameters-values-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="refreshParameterValues"></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
        <kendo-treelist ref="parameterValuesTree"
                        class="parameters-values-tree"
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
            <kendo-treelist-column :field="'value'" :title="'Value'"></kendo-treelist-column>
        </kendo-treelist>
    </div>
</template>
<style lang="scss">
    @import "./ParameterValues.scss";
</style>
<script src="./ParameterValues.controller.js"></script>