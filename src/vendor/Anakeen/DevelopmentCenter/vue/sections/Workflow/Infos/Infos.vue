<template>
    <div class="wfl-infos">
        <span class="wfl-title"><b>Identification</b></span>
        <div class="wfl-infos-content">
            <div class="wfl-infos-card-content-item">
                <span class="wfl-card-content-item-label">Name:</span>
                <span class="wfl-card-content-item-value" v-if="wflData.name !== null">{{wflData.name}}</span>
                <span class="wfl-card-content-item-value"
                      v-else> There is currently no name property for this workflow</span>
            </div>
            <div class="wfl-infos-card-content-item">
                <span class="wfl-card-content-item-label">Id:</span>
                <span class="wfl-card-content-item-value">{{wflData.id}}</span>
            </div>
            <div class="wfl-infos-card-content-item">
                <span class="wfl-card-content-item-label">Smart Structure:</span>
                <span class="wfl-card-content-item-value">
                    <a data-role="develRouterLink"
                       :href="`/devel/smartStructures/${wflData.structure}/infos`">{{wflData.structure}}</a></span>
            </div>

        </div>
        <div class="wfl-button-graph">

            <button title="Toggle references / label" v-on:click="toggleUseLabel" class="btn btn-primary"><i class="k-icon k-i-arrows-swap"></i></button>
            <button title="Download graph" v-on:click="downloadGraph" class="btn btn-primary"><i class="k-icon k-i-download"></i></button>
            <button title="Rotate graph" v-on:click="rotateGraph" class="btn btn-primary"><i class="k-icon k-i-rotate"></i></button>
        </div>
        <div class="wfl-graph" v-html="wflGraph"></div>
    </div>
</template>

<script src="./Infos.controller.js">
</script>
<style lang="scss">
    @import "./Infos.scss";
</style>
