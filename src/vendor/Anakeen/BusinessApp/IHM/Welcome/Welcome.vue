<template>
  <div class="ank-business-app-welcome">
    <div class="ank-business-app-welcome-creation card card-default">
      <div class="ank-business-app-welcome-creation-title card-header">
        <i class="material-icons">add</i> <span class="title">Creation</span>
      </div>
      <div class="ank-business-app-welcome-creation-content">
        <div
          v-for="(creation, index) in creation"
          :key="index"
          class="ank-business-app-welcome-creation-button card card-default"
          @click="onCreateClick(creation, $event)"
        >
            <span class="button-creation-label"><span class="creation-structure-icon-wrapper"><img class="creation-structure-icon" :src="creation.icon"/></span> <span class="creation-structure-title">{{creation.title||creation.name}}</span></span>
        </div>
      </div>
    </div>
    <div v-for="(gridCollec, index) in gridCollections" :key="index" class="ank-business-app-welcome-grid card card-default">
      <div class="ank-business-app-welcome-grid-title card-header">
        <i class="material-icons">grid_on</i> <span class="title">{{gridCollec.title}}</span>
      </div>
      <div class="ank-business-app-welcome-grid-content">
        <ank-se-grid :collection="gridCollec.name||gridCollec.initid" urlConfig="/api/v2/smartstructures/dsearch/gridConfig/<collection>"></ank-se-grid>

      </div>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./Welcome.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script lang="ts" src="./Welcome.component.ts"></script>
