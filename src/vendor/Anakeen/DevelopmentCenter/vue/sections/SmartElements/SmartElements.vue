<template>
    <div class="smart-elements-section">
        <ank-se-grid :urlConfig="urlConfig" filterable="inline" @before-grid-cell-render="cellRender" @action-click="actionClick"></ank-se-grid>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./SmartElements.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./SmartElements.controller.js"></script>
