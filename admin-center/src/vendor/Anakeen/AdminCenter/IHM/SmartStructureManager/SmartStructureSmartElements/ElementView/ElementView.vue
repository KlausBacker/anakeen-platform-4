<template>
  <div class="smart-element-view">
    <div v-if="isReady && !errorMessage" class="smart-element-view-toolbar">
      <div>
        Id :
        <b>{{ element.initid }}</b>
      </div>
      <button class="k-button k-button-icon" @click="onDetachElement">
        <i class="k-icon k-i-hyperlink-open"></i>
      </button>
    </div>
    <ank-smart-element
      ref="smartElement"
      class="smart-element"
      :initid="initid"
      :view-id="viewId"
      @ready="onReady"
      @beforeClose="refreshGrid"
    ></ank-smart-element>
    <div v-show="errorMessage" class="smart-element-error-view">
      <div class="smart-element-error-content">
        <i class="material-icons smart-element-error-icon">error_outline</i>
        <span class="smart-element-error-text">{{ errorMessage }}</span>
      </div>
    </div>
  </div>
</template>

<script src="./ElementView.controller.js" />

<style scoped lang="scss" src="./ElementView.scss" />
