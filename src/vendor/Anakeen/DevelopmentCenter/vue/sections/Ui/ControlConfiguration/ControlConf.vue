<template>
    <div ref="controlConfSplitter" class="control-conf-splitter">
        <div>
            <ank-se-grid ref="controlConfGrid"
                         :urlConfig="`/api/v2/devel/ui/smart/structures/${ssName}/control/config/`"
                         filterable="inline"
                         :pageSizes="[100, 200, 500]"
                         @action-click="actionClick">
            </ank-se-grid>
        </div>
        <div style="display: flex; flex: 1">
            <router-multi-view style="display:flex; flex: 1"></router-multi-view>
        </div>
    </div>
</template>
<style lang="scss">
    .control-conf-splitter {
        height: 100%;
        width: 100%;
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
</style>
<script src="./ControlConf.controller.js">
</script>
