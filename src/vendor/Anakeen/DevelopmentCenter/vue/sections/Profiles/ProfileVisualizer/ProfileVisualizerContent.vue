<template>
    <profile-grid style="height: 100%" v-if="seIdentifier" class="security-smartstructure-profile-grid" :profileId="seIdentifier"></profile-grid>
</template>

<script>
    import ProfileGrid from "../../../components/profile/profile.vue";
  export default {
    components: {
      ProfileGrid
    },
    props: ["seIdentifier"]
  }
</script>

<style scoped>

</style>