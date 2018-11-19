<template>
    <div ref="stepsSplitter" class="steps-splitter">
        <div class="steps-grid-parent">
            <kendo-datasource ref="stepsGrid"
                              :transport-read="getSteps"
                              :schema-data="parseStepsData">
            </kendo-datasource>
            <kendo-grid ref="stepsGridContent" class="steps-grid-content"
                        :data-source-ref="'stepsGrid'"
                        :sortable="true">
                <kendo-grid-column :field="'id'" :title="'<b>Step</b>'"></kendo-grid-column>
                <kendo-grid-column :field="'color'" :title="'<b>Color</b>'"
                                   :template="displayColor('color')"></kendo-grid-column>
                <kendo-grid-column :field="'label'" :title="'<b>Activity Label</b>'"></kendo-grid-column>
                <kendo-grid-column :field="'mailtemplates'" :title="'<b>Mail Templates</b>'"
                                   :template="displayMultiple('mailtemplate')"></kendo-grid-column>
                <kendo-grid-column :field="'timer'" :title="'<b>Timers</b>'"></kendo-grid-column>
                <kendo-grid-column :field="'viewcontrol'" :title="'<b>View Control</b>'"
                                   :template="displayLink('viewcontrol')"></kendo-grid-column>
                <kendo-grid-column :field="'mask'" :title="'<b>Masks</b>'"
                                   :template="displayLink('mask')"></kendo-grid-column>
                <kendo-grid-column :field="'profil'" :title="'<b>PDOC</b>'"
                                   :template="displayLink('profil')"></kendo-grid-column>
            </kendo-grid>
        </div>
        <div style="display: flex; flex: 1">
            <router-multi-view style="display:flex; flex: 1"></router-multi-view>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Steps.scss";
</style>
<script src="./Steps.controller.js"></script>