<template>
    <ank-splitter ref="stepsSplitter" class="steps-splitter" :panes="panes" localStorageKey="workflow-steps-splitter">
        <template slot="left">
            <div>
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
                                       :template="displayMultiple('mailtemplates')"></kendo-grid-column>
                    <kendo-grid-column :field="'timer'" :title="'<b>Timers</b>'"></kendo-grid-column>
                    <kendo-grid-column :field="'viewcontrol'" :title="'<b>View Control</b>'"
                                       :template="displayLink('viewcontrol')"></kendo-grid-column>
                    <kendo-grid-column :field="'mask'" :title="'<b>Masks</b>'"
                                       :template="displayLink('mask')"></kendo-grid-column>
                    <kendo-grid-column :field="'profil'" :title="'<b>PDOC</b>'"
                                       :template="displayLink('profil')"></kendo-grid-column>
                </kendo-grid>
            </div>
        </template>
        <template slot="right">
            <div style="display: flex;">
                <div v-if="splitterStepsEmpty" class="steps-se-empty">
                    <div>
                        <span class="k-icon k-i-information steps-se-empty-icon"></span>
                        <span class="steps-se-empty-text"> Please select an element to open </span>
                    </div>
                </div>
                <router-multi-view v-else style="display:flex; flex: 1" class="splitter-right"></router-multi-view>
            </div>
        </template>
    </ank-splitter>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Steps.scss";
</style>
<script src="./Steps.controller.js"></script>