<template>
    <div>
        <kendo-datasource ref="transitionsGrid"
                          :transport-read="getTransitions"
                          :schema-data="parseTransitionsData">
        </kendo-datasource>
        <kendo-grid ref="transitionsGridContent" class="transitions-grid-content"
                    :data-source-ref="'transitionsGrid'"
                    :filterable-mode="'row'"
                    :filterable-extra="false"
                    :sortable="false"
                    :resizable="true"
                    :noRecords="{ template: `<div class='empty-grid'>
                                                <div class='empty-grid-icon'>
                                                   <i class='material-icons'>grid_off</i>
                                                </div>
                                                <div class='empty-grid-text'>
                                                    <span> There are no Transitions for this Workflow</span>
                                                </div>
                                            </div>`}">
            <kendo-grid-column :field="'id'" :title="'<b>Transition</b>'"  :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'label'" :title="'<b>Label</b>'"  :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'mailtemplates'" :title="'<b>Mail Template</b>'"
                               :template="displayMultiple('mailtemplates')"  :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'volatileTimers'" :title="'<b>Volatile Timers</b>'"
                               :template="displayMultiple('volatileTimers')"  :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'persistentTimers'" :title="'<b>Persistent Timers</b>'"
                               :template="displayMultiple('persistentTimers')"  :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'unAttachTimers'" :title="'<b>Timers to unattach</b>'"
                               :template="displayMultiple('unAttachTimers')"  :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
        </kendo-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Transitions.scss";
</style>
<script src="./Transitions.controller.js"></script>