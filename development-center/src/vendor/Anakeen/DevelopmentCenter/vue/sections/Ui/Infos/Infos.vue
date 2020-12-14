<template>
  <div class="ui-infos">
    <span class="ui-title"
      ><b>{{ ssName }}</b> &nbsp Overview</span
    >
    <div class="ui-infos-content">
      <div v-if="cvdoc.id && cvdoc.name && cvdoc.title" class="ui-infos-card">
        <div class="ui-infos-card-title">View Controller</div>
        <div class="ui-infos-card-content">
          <div class="ui-infos-card-content-item">
            <span class="ui-card-content-item-label">Name:</span>
            <span class="ui-card-content-item-value"
              ><a data-role="develRouterLink" :href="`/devel/ui/${ssName}/control/element/${cvdoc.name}`">
                {{ cvdoc.name }}</a
              ></span
            >
          </div>
          <div class="ui-infos-card-content-item">
            <span class="ui-card-content-item-label">Identification:</span>
            <span class="ui-card-content-item-value">{{ cvdoc.id }}</span>
          </div>
          <div class="ui-infos-card-content-item">
            <span class="ui-card-content-item-label">Title:</span>
            <span class="ui-card-content-item-value">{{ cvdoc.title }}</span>
          </div>
        </div>
      </div>
      <div v-else class="ui-infos-card">
        <div class="ui-infos-card-title">View Controller</div>
        <div class="ui-infos-card-content">
          <div class="ui-infos-card-content-item">
            <span class="ui-card-content-item-value">No View Controller has been set for this Smart Structure...</span>
          </div>
        </div>
      </div>
      <div class="ui-infos-card">
        <div class="ui-infos-card-title">Render Access</div>
        <div class="ui-infos-card-content">
          <div v-if="renderaccess.renderAccessClass" class="ui-infos-card-content-item">
            <span class="ui-card-content-item-label">Access Class:</span>
            <span class="ui-card-content-item-value">{{ renderaccess.renderAccessClass }}</span>
          </div>
          <div v-else class="ui-infos-card-content-item">
            <span class="ui-card-content-item-value">No Render Access has been set for this Smart Structure...</span>
          </div>
        </div>
      </div>
      <div class="ui-infos-card">
        <div class="ui-infos-card-title">Primary Mask</div>
        <div class="ui-infos-card-content">
          <div v-if="mskfamid !== null" class="ui-infos-card-content-item">
            <span class="ui-card-content-item-label">Name:</span>
            <span class="ui-card-content-item-value"
              ><a data-role="develRouterLink" :href="`/devel/ui/${ssName}/masks/${mskfamid}`">{{ mskfamid }}</a></span
            >
          </div>
          <div v-else class="ui-infos-card-content-item">
            <span class="ui-card-content-item-value">No Primary Mask has been set for this View Controller...</span>
          </div>
        </div>
      </div>
      <div class="ui-infos-card">
        <div class="ui-infos-card-title">Infos</div>
        <div class="ui-infos-card-content" style="display: flex; justify-content: center; align-items: center">
          <info-nav :info-sections="infoSections"></info-nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script src="./Infos.controller.js"></script>
<style lang="scss">
@import "./Infos.scss";
</style>
