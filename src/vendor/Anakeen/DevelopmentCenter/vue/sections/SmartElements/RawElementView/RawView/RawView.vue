<template>
    <div :class="`data-raw-view data-raw-view--${type}`">
        <pre><code :class="type" v-html="formattedContent"></code></pre>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./RawView.scss";
</style>
<script src="./RawView.controller.js"></script>
