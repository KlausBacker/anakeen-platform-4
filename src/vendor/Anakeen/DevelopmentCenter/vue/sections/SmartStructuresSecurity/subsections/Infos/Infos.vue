<template>
    <div class="security-infos-section" v-if="isReady">
        <h1 class="security-title"><img class="security-icon" :src="structureProperties.icon"/> {{ssName}} ({{structureProperties.title}}) Security Overview</h1>
        <div class="security-infos-content">
            <div class="security-infos-card">
                <div class="infos-card-title">Profiles</div>
                <div class="infos-card-content">
                    <div class="infos-card-content-item">
                        <span class="card-content-item-label">Structure profile:</span>
                        <span class="card-content-item-value">
                            <router-link v-if="structureProperties.security && structureProperties.security.profil" :to="{name: 'Security::SmartStructures::Structures', params: { ssName: ssName}}">{{structureProperties.security.profil.name}} ({{structureProperties.security.profil.title}})</router-link>
                            <span v-else>No profile</span>
                        </span>
                    </div>
                    <div class="infos-card-content-item">
                        <span class="card-content-item-label">Elements profile:</span>
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
                </div>
            </div>
            <div class="security-infos-card">
                <div class="infos-card-title">Workflows</div>
                <div class="infos-card-content">
                </div>
            </div>
        </div>
    </div>
</template>

<script src="./Infos.controller.js">

</script>

<style lang="scss">
    .security-infos-section {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 1rem;
        .security-title {
            display: flex;
            align-items: center;
            .security-icon {
                margin-right: 1rem;
            }
        }

        .security-infos-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            flex: 1;
            overflow-y: auto;
            .security-infos-card {

                .infos-card-title {
                    font-size: 1.5rem;
                    margin-bottom: 1rem;
                }

                & + .security-infos-card {
                    /*margin-left: 1rem;*/
                }
                margin: auto;
                padding: .5rem;
                border: 1px solid rgb(210, 210, 210);
                display: inline-block;
                box-shadow: 3px 3px rgba(210, 210, 210, 0.5);
                height: 40%;
                width: 45%;
                min-width: 30%;

                .infos-card-content {
                    height: calc(100% - 2.8rem);
                    font-size: 1.1rem;
                    .card-content-item-label {
                        font-weight: bold;
                        margin-right: .5rem;
                    }
                }
            }
        }
    }
</style>