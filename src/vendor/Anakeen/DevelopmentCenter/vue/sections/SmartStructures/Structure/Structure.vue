<template>
    <div class="structure-parent">
        <div>
            <kendo-toolbar class="structure-toolbar">
                <kendo-toolbar-item type="button" icon="refresh" @click="refreshStructure"></kendo-toolbar-item>
            </kendo-toolbar>
        </div>
        <kendo-treelist ref="structureTree"
                        class="structure-tree"
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
            <kendo-treelist-column :template="columnTemplate('structure')" :field="'structure'" :title="'Structure'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('type')" :field="'type'" :title="'Type'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('labeltext')" :field="'labeltext'" :title="'Label'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('accessibility')" :field="'accessibility'" :title="'Access'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('ordered')" :field="'ordered'" :title="'Order'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('isTitle')" :field="'isTitle'" :title="'is Title'" :hidden="true"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('isAbstract')" :field="'isAbstract'" :title="'is Abstract'" :hidden="true"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('isNeeded')" :field="'isNeeded'" :title="'is Needed'" :hidden="true"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('phpconstraint')" :field="'phpconstraint'" :title="'Constraint'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('phpfunc')" :field="'phpfunc'" :title="'Function'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('phpfile')" :field="'phpfile'" :title="'PHP File'" :hidden="true"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('link')" :field="'link'" :title="'Link'" :hidden="true"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('optionValues')" :field="'optionValues'" :title="'Options'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('properties')" :field="'properties'" :title="'Properties'"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('overrides')" :field="'overrides'" :title="'Overrides'" :hidden="true"></kendo-treelist-column>
            <kendo-treelist-column :template="columnTemplate('declaration')" :field="'declaration'" :title="'Declaration'" :hidden="true"></kendo-treelist-column>
        </kendo-treelist>
    </div>
</template>
<style lang="scss">
    @import "./Structure.scss";
</style>
<script src="./Structure.controller.js"></script>
