<template>
    <div class="smart-elements-raw-view">
        <raw-view v-if="content" :type="formatType" :content="content" :parsed="isTypeParsed"></raw-view>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./RawElementView.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./RawElementView.controller.js"></script>
