<template>
    <div class="hub-station-main">
        <hub-station :config="config"></hub-station>
        <!--<header v-if="isHeaderEnabled" class="hub-station-bar hub-station-bar&#45;&#45;header">-->
            <!--<ank-dock ref="dockTop" @dockLoaded="onDockLoaded('top')" @tabSelected="onTabSelected('top', $event)" class="hub-dock hub-dock&#45;&#45;top" :content="content.top" :expanded="true" :expandable="false" position="top"></ank-dock>-->
        <!--</header>-->
        <!--<section class="hub-station-center-area">-->
            <!--<aside v-if="isLeftEnabled" class="hub-station-aside hub-station-left">-->
                <!--<ank-dock ref="dockLeft" @dockLoaded="onDockLoaded('left')" @tabSelected="onTabSelected('left', $event)" class="hub-dock hub-dock&#45;&#45;left" :content="content.left" position="left"></ank-dock>-->
            <!--</aside>-->
            <!--<section class="hub-station-content">-->
                <!--<router-multi-view class="hub-station-content-view"></router-multi-view>-->
            <!--</section>-->
            <!--<aside v-if="isRightEnabled" class="hub-station-right">-->
                <!--<ank-dock ref="dockRight" @dockLoaded="onDockLoaded('right')" @tabSelected="onTabSelected('right', $event)" class="hub-dock hub-dock&#45;&#45;right" :content="content.right" position="right"></ank-dock>-->
            <!--</aside>-->
        <!--</section>-->
        <!--<footer v-if="isFooterEnabled" class="hub-station-bar hub-station-bar&#45;&#45;footer">-->
            <!--<ank-dock ref="dockBottom" @dockLoaded="onDockLoaded('bottom')" @tabSelected="onTabSelected('bottom', $event)" class="hub-dock hub-dock&#45;&#45;bottom" :content="content.bottom" :expandable="false"  position="bottom"></ank-dock>-->
        <!--</footer>-->
    </div>
</template>

<!-- CSS to this component only -->
<style lang="scss">
    @import "./Hub.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./Hub.controller.js">
</script>

