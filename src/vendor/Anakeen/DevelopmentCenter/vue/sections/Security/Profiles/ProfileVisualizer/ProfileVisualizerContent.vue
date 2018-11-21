<template>
    <profile-grid style="width: 100%;" v-if="seIdentifier" class="security-smartstructure-profile-grid" :onRefProfileClickCallback="onRefProfilClick" :detachable="true" :profileId="seIdentifier"></profile-grid>
</template>

<script>
    import ProfileGrid from "../../../../components/profile/profile.vue";
  export default {
    components: {
      ProfileGrid
    },
    props: ["seIdentifier"],
    methods: {
      onRefProfilClick(refProfile) {
        this.$router.push(`/devel/security/profiles/${refProfile.name||refProfile.id}`);
      }
    }
  }
</script>

<style scoped>

</style>