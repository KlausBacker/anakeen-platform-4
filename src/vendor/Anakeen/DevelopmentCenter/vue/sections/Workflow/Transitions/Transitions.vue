<template>
    <ank-splitter ref="ankSplitter" class="masks-splitter" :panes="panes" localStorageKey="workflow-transitions-splitter">
        <template slot="left">
            <div>
                <kendo-datasource ref="transitionsGrid"
                                  :transport-read="getTransitions"
                                  :schema-data="parseTransitionsData">
                </kendo-datasource>
                <kendo-grid ref="transitionsGridContent" class="transitions-grid-content"
                            :data-source-ref="'transitionsGrid'"
                            :sortable="true">
                    <kendo-grid-column :field="'id'" :title="'<b>Transition</b>'"></kendo-grid-column>
                    <kendo-grid-column :field="'label'" :title="'<b>Label</b>'"></kendo-grid-column>
                    <kendo-grid-column :field="'mailtemplates'" :title="'<b>Mail Template</b>'"
                                       :template="displayMultiple('mailtemplates')"></kendo-grid-column>
                    <kendo-grid-column :field="'volatileTimers'" :title="'<b>Volatile Timers</b>'"
                                       :template="displayMultiple('volatileTimers')"></kendo-grid-column>
                    <kendo-grid-column :field="'persistentTimers'" :title="'<b>Persistent Timers</b>'"
                                       :template="displayMultiple('persistentTimers')"></kendo-grid-column>
                    <kendo-grid-column :field="'unAttachTimers'" :title="'<b>Timers to unattach</b>'"
                                       :template="displayMultiple('unAttachTimers')"></kendo-grid-column>
                </kendo-grid>
            </div>
        </template>
        <template slot="right">
            <div style="display: flex;">
                <div v-if="splitterTransitionEmpty" class="transitions-se-empty">
                    <div>
                        <span class="k-icon k-i-information transitions-se-empty-icon"></span>
                        <span class="transitions-se-empty-text"> Please select an element to open </span>
                    </div>
                </div>
                <router-multi-view v-else style="display:flex; flex: 1" class="splitter-right"></router-multi-view>
            </div>
        </template>
    </ank-splitter>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Transitions.scss";
</style>
<script src="./Transitions.controller.js"></script>