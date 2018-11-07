<template>
    <div ref="controlConfSplitter" class="control-conf-splitter">
        <div>
            <ank-se-grid collection="CVDOC"
                         filterable="inline"
                         @action-click="actionClick">
            <ank-se-grid-column :field="'name'" :title="'Name'"></ank-se-grid-column>
            <ank-se-grid-column :field="'cv_renderaccessclass'" :title="'Render Access'"></ank-se-grid-column>
            <ank-se-grid-column :field="'cv_primarymask'" :title="'Primary Mask'"></ank-se-grid-column>
                <ank-se-grid-actions>
                    <ank-se-grid-action action="consult"></ank-se-grid-action>
                </ank-se-grid-actions>
            </ank-se-grid>
        </div>
        <div>
            <ank-smart-element ref="controlConfConsult" class="control-conf-consult-content"></ank-smart-element>
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
</style>
<script src="./ControlConf.controller.js">
</script>
