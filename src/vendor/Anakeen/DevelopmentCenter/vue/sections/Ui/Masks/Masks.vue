<template>
    <div ref="masksSplitter" class="masks-splitter">
        <div>
            <ank-se-grid ref="masksGrid"
                         :urlConfig="`/api/v2/devel/ui/smart/structures/${ssName}/masks/config/`"
                         filterable="inline"
                         :pageSizes="[100,200,500]"
                         @action-click="actionClick">
            </ank-se-grid>
        </div>
        <div style="display: flex; flex: 1">
            <router-multi-view style="display:flex; flex: 1"></router-multi-view>
        </div>
    </div>
</template>
<style lang="scss">
    .masks-splitter {
        height: 100%;
        width: 100%;
    }
    .masks-consult-content {
        width: 100%;
        height: 100%;
    }
    .masks-view-is-opened {
        background: #157EFB !important;
        color: white !important;
        .action-label {
            color: white !important;
        }
    }
    .masks-view-is-opened:hover {
        background: white !important;
        color: inherit !important;
        .action-label {
            color: #157EFB !important;
        }
    }
</style>
<script src="./Masks.controller.js">
</script>
