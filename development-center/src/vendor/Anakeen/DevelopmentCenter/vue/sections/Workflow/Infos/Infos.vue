<template>
  <div class="wfl-infos">
    <span class="wfl-title"><b>Identification</b></span>
    <div class="wfl-infos-content">
      <div class="wfl-infos-card-content-item">
        <span class="wfl-card-content-item-label">Name:</span>
        <span v-if="wflData.name !== null" class="wfl-card-content-item-value">{{ wflData.name }}</span>
        <span v-else class="wfl-card-content-item-value"> There is currently no name property for this workflow</span>
      </div>
      <div class="wfl-infos-card-content-item">
        <span class="wfl-card-content-item-label">Id:</span>
        <span class="wfl-card-content-item-value">{{ wflData.id }}</span>
      </div>
      <div class="wfl-infos-card-content-item">
        <span class="wfl-card-content-item-label">Smart Structure:</span>
        <span class="wfl-card-content-item-value">
          <a data-role="develRouterLink" :href="`/devel/smartStructures/${wflData.structure}/infos`">{{
            wflData.structure
          }}</a></span
        >
      </div>
    </div>
    <div class="wfl-button-graph">
      <button title="Toggle display labels : state / activity / raw" class="btn btn-primary" @click="toggleUseLabel">
        <i class="k-icon k-i-arrows-swap"></i>
      </button>
      <button title="Download graph" class="btn btn-primary" @click="downloadGraph">
        <i class="k-icon k-i-download"></i>
      </button>
      <button title="Rotate graph" class="btn btn-primary" @click="rotateGraph">
        <i class="k-icon k-i-rotate"></i>
      </button>
    </div>
    <div class="wfl-graph" v-html="wflGraph"></div>
  </div>
</template>

<script src="./Infos.controller.js"></script>
<style lang="scss">
@import "./Infos.scss";
</style>
