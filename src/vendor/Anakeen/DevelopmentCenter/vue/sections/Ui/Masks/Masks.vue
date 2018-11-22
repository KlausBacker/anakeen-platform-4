<template>
    <ank-splitter ref="masksSplitter" class="masks-splitter" :panes="panes" localStorageKey="ui-masks-splitter">
        <template slot="left">
            <ank-se-grid ref="masksGrid"
                         :urlConfig="`/api/v2/devel/ui/smart/structures/${ssName}/masks/config/`"
                         filterable="inline"
                         :pageSizes="[100,200,500]"
                         @action-click="actionClick">
            </ank-se-grid>
        </template>
        <template slot="right">
            <router-multi-view style="display:flex; flex: 1" class="splitter-right"></router-multi-view>
        </template>
    </ank-splitter>
</template>
<style lang="scss">
    .masks-splitter {
        height: 100%;
        width: 100%;

        .grid-cell--name {
            word-break: break-all;
        }
    }

    .masks-consult-content {
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
        width : 100%;
        height: 100%;
        .smart-element-view {
            width : 100%;
            height: 100%;
        }
    }
</style>
<script src="./Masks.controller.js">
</script>
