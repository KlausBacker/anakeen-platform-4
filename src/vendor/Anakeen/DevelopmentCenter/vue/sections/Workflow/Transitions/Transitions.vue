<template>
    <div class="transitions-grid-parent">
        <kendo-datasource ref="transitionsGrid"
                          :transport-read="getTransitions"
                          :schema-data="parseTransitionsData">
        </kendo-datasource>
        <kendo-grid ref="transitionsGridContent" class="transitions-grid-content"
                    :data-source-ref="'transitionsGrid'"
                    :sortable="true">
            <kendo-grid-column :field="'id'" :title="'<b>Transition</b>'"></kendo-grid-column>
            <kendo-grid-column :field="'mail'" :title="'<b>Mail Template</b>'"></kendo-grid-column>
            <kendo-grid-column :field="'colatileTimers'" :title="'<b>Volatile Timers</b>'" :template="displayMultiple"></kendo-grid-column>
            <kendo-grid-column :field="'persistentTimers'" :title="'<b>Persistent Timers</b>'" :template="displayMultiple"></kendo-grid-column>
            <kendo-grid-column :field="'unAttachTimers'" :title="'<b>Timers to unattach</b>'" :template="displayMultiple"></kendo-grid-column>
        </kendo-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Transitions.scss";
</style>
<script src="./Transitions.controller.js"></script>