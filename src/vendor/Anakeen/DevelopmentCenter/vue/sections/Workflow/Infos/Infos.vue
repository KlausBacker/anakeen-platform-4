<template>
    <div class="wfl-infos">
        <span class="wfl-title"><b>Overview</b></span>
        <object :data="wflGraph" type="image/svg+xml" class="wfl-graph-card"></object>
        <div class="wfl-infos-content">
            <div class="wfl-infos-card">
                <div class="wfl-infos-card-title">Identification</div>
                <div class="wfl-infos-card-content">
                    <div class="wfl-infos-card-content-item">
                        <span class="wfl-card-content-item-label">Name:</span>
                        <span class="wfl-card-content-item-value" v-if="wflData.name !== null">{{wflData.name}}</span>
                        <span class="wfl-card-content-item-value" v-else> There is currently no name property for this workflow</span>
                    </div>
                    <div class="wfl-infos-card-content-item">
                        <span class="wfl-card-content-item-label">Identification:</span>
                        <span class="wfl-card-content-item-value">{{wflData.id}}</span>
                    </div>
                    <div class="wfl-infos-card-content-item">
                        <span class="wfl-card-content-item-label">Smart Structure:</span>
                        <span class="wfl-card-content-item-value">{{wflData.structure}}</span>
                    </div>
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
