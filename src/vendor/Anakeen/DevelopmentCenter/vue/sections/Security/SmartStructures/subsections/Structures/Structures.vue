<template>
    <div class="security-smartstructure-profile">
        <div class="security-smartstructure-profile-nocontent" v-if="!profid">
            <div class="security-smartstructure-profile-nocontent-icon">
                <i class="material-icons">{{noContentIcon}}</i>
                <i v-if="noProfile" class="material-icons disable">not_interested</i>
            </div>
            <div class="security-smartstructure-profile-nocontent-text">{{profilWaitingLabel}}</div>
        </div>
        <div class="security-smartstructure-profile-content" v-else>
            <profile-grid class="security-smartstructure-profile-grid" :profileId="profid" :defaultColumns="['create', 'icreate']"></profile-grid>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./Structures.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./Structures.controller.js"></script>
