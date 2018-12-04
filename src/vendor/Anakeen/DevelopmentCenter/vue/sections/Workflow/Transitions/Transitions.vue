<template>
    <div>
        <kendo-datasource ref="transitionsGrid"
                          :transport-read="getTransitions"
                          :schema-data="parseTransitionsData">
        </kendo-datasource>
        <kendo-grid ref="transitionsGridContent" class="transitions-grid-content"
                    :data-source-ref="'transitionsGrid'"
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
            <kendo-grid-column :title="'<b>Transition</b>'" :template="displayData('transition')"></kendo-grid-column>
            <kendo-grid-column :title="'<b>Mail Template</b>'"
                               :template="displayLink('mailtemplates')"></kendo-grid-column>
            <kendo-grid-column :title="'<b>Timers</b>'" :template="displayLink('timers')"></kendo-grid-column>
        </kendo-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Transitions.scss";
</style>
<script src="./Transitions.controller.js"></script>