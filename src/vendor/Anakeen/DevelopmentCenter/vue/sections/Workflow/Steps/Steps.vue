<template>
    <div class="steps-grid-parent">
        <kendo-datasource ref="stepsGrid"
                          :transport-read="getSteps"
                          :schema-data="parseStepsData">
        </kendo-datasource>
        <kendo-grid ref="stepsGridContent" class="steps-grid-content"
                    :data-source-ref="'stepsGrid'"
                    :sortable="true">
            <kendo-grid-column :field="'id'" :title="'<b>Step</b>'":filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'color'" :title="'<b>Color</b>'"  :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'label'" :title="'<b>Activity Label</b>'"  :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'param.mailTemplate'" :title="'<b>Mail Template</b>'"  :filterable-cell-operator="'contains'" :filterable-cell-show-operators="false" :filterable-cell-template="autoFilterCol"></kendo-grid-column>
            <kendo-grid-column :field="'param.timer'" :title="'<b>Timers</b>'" :filterable-cell-template="disabledFilter" :filterable-cell-show-operators="false"></kendo-grid-column>
            <kendo-grid-column :field="'viewcontrol'" :title="'<b>View Control</b>'" :filterable-cell-template="disabledFilter" :filterable-cell-show-operators="false"></kendo-grid-column>
            <kendo-grid-column :field="'mask'" :title="'<b>Masks</b>'" :filterable-cell-template="disabledFilter" :filterable-cell-show-operators="false"></kendo-grid-column>
            <kendo-grid-column :field="'profil'" :title="'<b>PDOC</b>'" :filterable-cell-template="disabledFilter" :filterable-cell-show-operators="false"></kendo-grid-column>
            <!--<kendo-grid-column :field="'security.seAccess'" :title="'<b>Smart Field Access</b>'" :filterable-cell-template="disabledFilter" :filterable-cell-show-operators="false"></kendo-grid-column>-->
        </kendo-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Steps.scss";
</style>
<script src="./Steps.controller.js"></script>