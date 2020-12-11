<template>
  <div class="security-fieldaccess-profile">
    <div v-if="empty" class="security-fieldaccess-profile-nocontent">
      <div class="security-fieldaccess-profile-nocontent-icon">
        <i class="material-icons">{{ noContentIcon }}</i>
        <i class="material-icons disable">not_interested</i>
      </div>
      <div class="security-fieldaccess-profile-nocontent-text" :data-text="profilWaitingLabel">
        {{ profilWaitingLabel }}
      </div>
    </div>
    <div v-else-if="cfallid" class="security-fieldaccess-profile-content">
      <profile-grid
        class="security-fieldaccess-profile-grid"
        :profile-id="cfallid"
        :only-extended-acls="true"
      ></profile-grid>
    </div>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./Fields.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./Fields.controller.js"></script>
