<template>
  <div class="ank-business-app-welcome">
    <div v-if="creation && creation.length"
class="ank-business-app-welcome-creation card card-default">
      <div class="ank-business-app-welcome-creation-title card-header">
        <span class="ank-business-app-welcome-grid-title-label">
          <i class="title-icon material-icons">add</i> <span class="title">Creation</span>
        </span>
      </div>
      <div class="ank-business-app-welcome-creation-content">
        <div
          v-for="(item, index) in creation"
          :key="index"
          class="ank-business-app-welcome-creation-button card card-default"
          @click="onCreateClick(item, $event)"
        >
          <span class="button-creation-label"
            ><span class="creation-structure-icon-wrapper"
              ><img class="creation-structure-icon" :src="item.icon"
            /></span>
            <span class="creation-structure-title">{{ item.title || item.name }}</span></span
          >
        </div>
      </div>
    </div>
    <div
      v-for="(gridCollec, index) in gridCollections"
      :key="index"
      class="ank-business-app-welcome-grid card card-default"
    >
      <div class="ank-business-app-welcome-grid-title card-header">
        <span class="ank-business-app-welcome-grid-title-label">
          <i class="title-icon material-icons">grid_on</i>
          <span class="title">{{ gridCollec.title }}</span>
        </span>
        <button class="ank-business-app-welcome-grid-reload k-button" @click="reloadGrid(index)">
          <i class="fa fa-refresh" />
        </button>
      </div>
      <div class="ank-business-app-welcome-grid-content">
        <ank-se-grid
          ref="grids"
          :collection="gridCollec.name || gridCollec.initid"
          controller="DEFAULT_GRID_CONTROLLER"
          defaultExpandable
          :actions="[{ action: 'consult', title: 'Display' }]"
          @RowActionClick="onActionClick"
        />
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
