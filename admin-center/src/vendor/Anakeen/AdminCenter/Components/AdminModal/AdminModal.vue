<template>
    <kendo-dialog
                  :title="title"
                  :content="template"
                  :width="width"
                  :visible="visible"
                  :modal="modal"
                  :closable="closable"
                  ref="kendoDialog"
    >
        <kendo-dialog-action v-for="(action, index) in actions" :key="`action-${index}`" :text="action.text" :primary="action.primary" :action="getAction(action.action)" ></kendo-dialog-action>
    </kendo-dialog>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./AdminModal.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./AdminModal.component.js"></script>
