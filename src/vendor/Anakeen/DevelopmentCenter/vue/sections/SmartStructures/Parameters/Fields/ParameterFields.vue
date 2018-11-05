<template>
    <div class="parameters-fields-parent">
        <div>
            <kendo-toolbar class="parameters-fields-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="refreshParameterFields"></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
        <kendo-treelist ref="parameterFieldsTree"
                        class="parameters-fields-tree"
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
            <kendo-treelist-column :template="columnTemplate('labeltext')" :field="'labeltext'" :title="'Label'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('type')" :field="'type'" :title="'Type'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('ordered')" :field="'ordered'" :title="'Order'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('accessibility')" :field="'accessibility'" :title="'Access'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('link')" :field="'link'" :title="'Link'" :hidden="true"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('phpfile')" :field="'phpfile'" :title="'PHP File'" :hidden="true"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('phpfunc')" :field="'phpfunc'" :title="'Function'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('phpconstraint')" :field="'phpconstraint'" :title="'Constraint'"></kendo-treelist-column>
        </kendo-treelist>
    </div>
</template>
<style lang="scss">
    @import "ParameterFields.scss";
</style>
<script src="./ParameterFields.controller.js"></script>