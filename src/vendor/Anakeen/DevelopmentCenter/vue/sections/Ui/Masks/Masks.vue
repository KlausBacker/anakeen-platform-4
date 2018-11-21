<template>
    <ank-splitter ref="masksSplitter" class="masks-splitter" :panes="panes">
        <template slot="left">
            <ank-se-grid ref="masksGrid"
                         :urlConfig="`/api/v2/devel/ui/smart/structures/${ssName}/masks/config/`"
                         filterable="inline"
                         :pageSizes="[100,200,500]"
                         @action-click="actionClick">
            </ank-se-grid>
        </template>
        <template slot="right">
            <div style="display: flex;">
                <div v-if="splitterMasksEmpty" class="masks-se-empty">
                    <div>
                        <span class="k-icon k-i-information masks-se-empty-icon"></span>
                        <span class="masks-se-empty-text"> Please select an element to open </span>
                    </div>
                </div>
                <router-multi-view v-else style="display:flex; flex: 1" class="splitter-right"></router-multi-view>
            </div>
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
    .splitter-right {
        width : 100%;
        height: 100%;
        .smart-element-view {
            width : 100%;
            height: 100%;
        }
    }
    .masks-se-empty {
        flex: 1;
        align-items: center;
        justify-content: center;
        display: flex;
        .masks-se-empty-icon {
            display: block;
            font-size: 10rem;
            padding-bottom: 5rem;
            padding-left: 6.5rem;
            color: #A4A4A4 !important;
        }
        .masks-se-empty-text {
            font-size: 1.5rem;
            color: #A4A4A4 !important;
        }
    }
</style>
<script src="./Masks.controller.js">
</script>
