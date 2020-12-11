<template>
  <profile-grid
    v-if="seIdentifier"
    style="width: 100%;"
    class="security-smartstructure-profile-grid"
    :on-ref-profile-click-callback="onRefProfilClick"
    :detachable="true"
    :profile-id="seIdentifier"
  ></profile-grid>
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
      this.$router.push(`/devel/security/profiles/${refProfile.name || refProfile.id}`);
    }
  }
};
</script>

<style scoped></style>
