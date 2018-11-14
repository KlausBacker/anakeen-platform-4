<template>
    <profile-grid style="width: 100%;" v-if="seIdentifier" class="security-smartstructure-profile-grid" :detachable="true" :profileId="seIdentifier"></profile-grid>
</template>

<script>
    import ProfileGrid from "../../../../components/profile/profile.vue";
  export default {
    components: {
      ProfileGrid
    },
    props: ["seIdentifier"]
  }
</script>

<style scoped>

</style>