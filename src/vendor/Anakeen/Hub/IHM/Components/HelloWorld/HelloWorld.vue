<template>
    <div v-if="isDockCollapsed" class="hello-world-collapsed-component">
       <i class="fa fa-edit"></i>
    </div>
    <div v-else-if="isDockExpanded" class="hello-world-expanded-component">
        <i class="fa fa-edit"></i><span>{{msg}}</span>
    </div>
    <div v-else-if="isHubContent">
        My beautiful Hello World with propsData = { msg: '{{msg}}', test: {{test}} }
        <router-link :to="resolveHubSubPath('tutu')">Click</router-link>
        <router-view></router-view>
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
