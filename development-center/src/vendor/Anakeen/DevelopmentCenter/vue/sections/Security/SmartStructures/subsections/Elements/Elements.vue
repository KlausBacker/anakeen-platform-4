<template>
  <div class="security-smartelement-profile">
    <div v-if="empty" class="security-smartelement-profile-nocontent">
      <div class="security-smartelement-profile-nocontent-icon">
        <i class="material-icons">{{ noContentIcon }}</i>
        <i class="material-icons disable">not_interested</i>
      </div>
      <div class="security-smartelement-profile-nocontent-text" :data-text="profilWaitingLabel">
        {{ profilWaitingLabel }}
      </div>
    </div>
    <div v-else-if="cprofid" class="security-smartelement-profile-content">
      <profile-grid
        class="security-smartelement-profile-grid"
        :profile-id="cprofid"
        :default-columns="['view', 'edit', 'delete']"
      ></profile-grid>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./Elements.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./Elements.controller.js"></script>
