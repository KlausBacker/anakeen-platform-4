<template>
    <div class="hub-parent">
        <header class="hub-header">
            <div class="hub-header__content">
                <span>Hub</span>
            </div>
            <div class="hub-header__content">
                <identity :large="true" :email-alterable="true" :password-alterable="true"></identity>
                <logout></logout>
            </div>
        </header>
        <div class="hub-content">
            <dock class="hub-dock" :content="content"></dock>
        </div>
    </div>
</template>

<!-- CSS to this component only -->
<style lang="scss">
    @import "Hub";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./Hub.controller.js"></script>

