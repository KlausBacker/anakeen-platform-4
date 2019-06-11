<template>
    <div>
        <kendo-datasource ref="stepsGrid"
                          :transport-read="getSteps"
                          :schema-data="parseStepsData">
        </kendo-datasource>
        <kendo-grid ref="stepsGridContent" class="steps-grid-content"
                    :data-source-ref="'stepsGrid'"
                    :sortable="false"
                    :resizable="true"
                    :noRecords="{ template: `<div class='empty-grid'>
                                                <div class='empty-grid-icon'>
                                                   <i class='material-icons'>grid_off</i>
                                                </div>
                                                <div class='empty-grid-text'>
                                                    <span> There are no Steps for this Workflow</span>
                                                </div>
                                            </div>`}">
            <kendo-grid-column :title="'<b>Step</b>'" :template="displayData('step')"></kendo-grid-column>
            <kendo-grid-column :title="'<b>Settings</b>'" :template="displayLink('settings')"></kendo-grid-column>
            <kendo-grid-column :title="'<b>UI</b>'" :template="displayLink('UI')"></kendo-grid-column>
            <kendo-grid-column :title="'<b>Security</b>'" :template="displayLink('security')"></kendo-grid-column>
        </kendo-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Steps.scss";
</style>
<script src="./Steps.controller.js"></script>