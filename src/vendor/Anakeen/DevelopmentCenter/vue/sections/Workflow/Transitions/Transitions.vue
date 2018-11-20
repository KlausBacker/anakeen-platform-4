<template>
    <div ref="transitionsSplitter" class="transitions-splitter">
        <div class="transitions-grid-parent">
            <kendo-datasource ref="transitionsGrid"
                              :transport-read="getTransitions"
                              :schema-data="parseTransitionsData">
            </kendo-datasource>
            <kendo-grid ref="transitionsGridContent" class="transitions-grid-content"
                        :data-source-ref="'transitionsGrid'"
                        :sortable="true">
                <kendo-grid-column :field="'id'" :title="'<b>Transition</b>'"></kendo-grid-column>
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
        <div style="display: flex; flex: 1">
            <router-multi-view style="display:flex; flex: 1"></router-multi-view>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Transitions.scss";
</style>
<script src="./Transitions.controller.js"></script>