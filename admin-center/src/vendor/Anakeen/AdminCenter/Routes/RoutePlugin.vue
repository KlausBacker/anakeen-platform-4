<template>
    <div>
        <div class="tabstrip">
            <ul>
                <li class="k-state-active">
                    Routes
                </li>
                <li>
                    Middleware
                </li>
            </ul>
            <div id="list-routes" class="list-routes">
                <div id="routes-tree" class="routes-tree"></div>
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
