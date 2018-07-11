<template>
    <div>
        <div class="tabstrip">
            <ul>
                <li class="routes-tab k-state-active">
                    Routes
                </li>
                <li class="middlewares-tab">
                    Middlewares
                </li>
            </ul>
            <div id="list-routes" class="list-routes">
                <div id="routes-tree" class="routes-tree"></div>
            </div>
            <div id="list-middlewares" class="list-middlewares">
                <div id="middlewares-tree" class="middlewares-tree"></div>
            </div>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "RoutePlugin.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./RoutePlugin.controller.js"></script>
