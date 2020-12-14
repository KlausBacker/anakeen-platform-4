<template>
  <div class="security-smartstructure-profile">
    <div v-if="!profid" class="security-smartstructure-profile-nocontent">
      <div class="security-smartstructure-profile-nocontent-icon">
        <i class="material-icons">{{ noContentIcon }}</i>
        <i v-if="noProfile" class="material-icons disable">not_interested</i>
      </div>
      <div class="security-smartstructure-profile-nocontent-text">{{ profilWaitingLabel }}</div>
    </div>
    <div v-else class="security-smartstructure-profile-content">
      <profile-grid
        class="security-smartstructure-profile-grid"
        :profile-id="profid"
        :default-columns="['create', 'icreate']"
      ></profile-grid>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./Structures.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./Structures.controller.js"></script>
