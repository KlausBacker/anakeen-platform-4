<template>
    <div class="hello-world-component">
        <div>this is template body of HelloWorld component <span v-if="msg">: {{msg}}</span></div>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./HelloWorld.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./HelloWorld.component.js"></script>
