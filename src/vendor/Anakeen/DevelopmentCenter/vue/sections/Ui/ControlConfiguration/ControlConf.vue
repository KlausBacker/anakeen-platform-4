<template>
    <ank-splitter ref="controlSplitter" class="control-conf-splitter" :panes="panes" localStorageKey="ui-control-splitter">
        <template slot="left">
            <ank-se-grid ref="controlConfGrid"
                         :urlConfig="`/api/v2/devel/ui/smart/structures/${ssName}/control/config/`"
                         filterable="inline"
                         :pageSizes="[100, 200, 500]"
                         :sortable="''"
                         @action-click="actionClick"
                         @grid-ready="getFiltered">
            </ank-se-grid>
        </template>
        <template slot="right">
            <component style="height: 100%" v-if="selectedControl" :is="selectedControl.component" v-bind="selectedControl.props"></component>
        </template>
    </ank-splitter>
</template>
<style lang="scss">
    .control-conf-splitter {
        height: 100%;
        width: 100%;

        .grid-cell--name {
            word-break: break-all;
        }
    }

    .control-conf-consult-content {
        width: 100%;
        height: 100%;
    }

    .control-view-is-opened {
        background: #157EFB !important;
        color: white !important;
        .action-label {
            color: white !important;
        }
    }

    .operator-label {
        display: none;
    }
    .k-dropdown-operator {
        display: none;
    }
    .splitter-right {
        width: 100%;
        height: 100%;
        .smart-element-view {
            width: 100%;
            height: 100%;
            overflow: auto;
        }
    }
</style>
<script src="./ControlConf.controller.js">
</script>
