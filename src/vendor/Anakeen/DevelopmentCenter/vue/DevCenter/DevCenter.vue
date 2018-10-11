<template>
    <div class="dev-center-app">
        <dev-header></dev-header>
        <div class="dev-center-content">
            <dev-side-menu></dev-side-menu>
            <router-view></router-view>
        </div>
        <ank-notifier ref="ankNotifier" position="top-right"
                      defaultType="notice"></ank-notifier>
    </div>

</template>

<script src="./DevCenter.controller.js">
</script>

<style lang="scss">
    html, body {
        width: 100vw;
        height: 100vh;
        display: flex;
        font-size: 12px;
    }

    .dev-center-app {
        display: flex;
        flex-direction: column;
        flex:1;

        .dev-center-content {
            display: flex;
            flex: 1;
        }
    }
</style>