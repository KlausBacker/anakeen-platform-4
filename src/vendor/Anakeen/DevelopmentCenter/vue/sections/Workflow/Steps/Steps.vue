<template>
    <div>
        <kendo-datasource ref="stepsGrid"
                          :transport-read="getSteps"
                          :schema-data="parseStepsData">
        </kendo-datasource>
        <kendo-grid ref="stepsGridContent" class="steps-grid-content"
                    :data-source-ref="'stepsGrid'"
                    :filterable-mode="'row'"
                    :filterable-extra="false"
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
            <kendo-grid-column :field="'id'" :title="'<b>Step</b>'" :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'label'" :title="'<b>Label</b>'" :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'color'" :title="'<b>Color</b>'"
                               :template="displayColor('color')" :width="'8rem'" :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'activity'" :title="'<b>Activity</b>'" :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'mailtemplates'" :title="'<b>Mail Templates</b>'"
                               :template="displayMultiple('mailtemplates')" :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'timer'" :title="'<b>Timers</b>'"
                               :template="displayLink('timer')" :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'viewcontrol'" :title="'<b>View Control</b>'"
                               :template="displayLink('viewcontrol')" :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'mask'" :title="'<b>Masks</b>'"
                               :template="displayLink('mask')" :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'profil'" :title="'<b>PDOC</b>'"
                               :template="displayLink('profil')" :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'fall'" :title="'<b>Smart Field Access</b>'"
                               :template="displayLink('fall')" :filterable-cell-operator="'contains'"
                               :filterable-cell-show-operators="false"
                               :filterable-cell-template="autoFilterCol"></kendo-grid-column>
        </kendo-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Steps.scss";
</style>
<script src="./Steps.controller.js"></script>