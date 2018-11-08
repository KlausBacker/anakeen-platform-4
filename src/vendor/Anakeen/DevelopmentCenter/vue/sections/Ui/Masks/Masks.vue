<template>
    <div ref="masksSplitter" class="masks-splitter">
        <div>
            <ank-se-grid :urlConfig="`/api/v2/devel/ui/smart/structures/${ssName}/masks/config/`"
                         filterable="inline"
                         @action-click="actionClick">
            </ank-se-grid>
        </div>
        <div>
            <ank-smart-element ref="masksConsult" class="masks-consult-content"></ank-smart-element>
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
</style>
<script src="./Masks.controller.js">
</script>
