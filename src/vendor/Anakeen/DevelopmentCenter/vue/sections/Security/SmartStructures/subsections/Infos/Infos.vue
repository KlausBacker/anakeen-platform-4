<template>
    <div class="security-infos-section" v-if="isReady">
        <h1 class="security-title">
            <img class="security-icon" :src="structureProperties.icon"/> {{ssName}} ({{structureProperties.title}}) Security Overview</h1>
        <div class="security-infos-content">
            <div class="security-infos-card">
                <div class="infos-card-title">Profiles</div>
                <div class="infos-card-content">
                    <div class="infos-card-content-item">
                        <span class="card-content-item-label">Structure profile:</span>
                        <span class="card-content-item-value">
                            <router-link v-if="structureProperties.security && structureProperties.security.profil && structureProperties.security.profil.id" :to="{name: 'Security::SmartStructures::Structures', params: { ssName: ssName}}">{{structureProperties.security.profil.name}} ({{structureProperties.security.profil.title}})</router-link>
                            <span v-else>No profile</span>
                        </span>
                    </div>
                    <div class="infos-card-content-item">
                        <span class="card-content-item-label">Default Element profile:</span>
                        <span class="card-content-item-value">
                            <router-link v-if="structureSecurity.cprofid" :to="{name: 'Security::SmartStructures::Elements', params: { ssName: ssName}}">{{structureSecurity.cprofid.name}} ({{structureSecurity.cprofid.title}})</router-link>
                            <span v-else>No profile</span>
                        </span>
                    </div>
                    <div class="infos-card-content-item">
                        <span class="card-content-item-label">Field Access:</span>
                        <span class="card-content-item-value">
                            <router-link v-if="structureSecurity.cfallid" :to="{name: 'Security::SmartStructures::Fields', params: { ssName: ssName}}">{{structureSecurity.cfallid.name}} ({{structureSecurity.cfallid.title}})</router-link>
                            <span v-else>No profile</span>
                        </span>
                    </div>
                    <button class="dynamic-profiles-button k-button k-button-icontext k-primary" @click="onDynamicProfilesClick"><span class="k-icon"><i class="material-icons">navigation</i></span>Dynamic Profiles</button>
                </div>
            </div>
            <div class="security-infos-card">
                <div class="infos-card-title">Infos</div>
                <div class="infos-card-content" style="display: flex; justify-content: center; align-items: center">
                    <infos-nav :infoSections="infoSections"></infos-nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script src="./Infos.controller.js">

</script>

<style lang="scss">
    @import "./Infos.scss";
</style>