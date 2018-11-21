<template>
    <div class="permissions-grid-parent">
        <profile-view ref="permGrid" :profileId="wflName" onlyExtendedAcls="true" class="permissions-grid-content"></profile-view>
    </div>
</template>
<style lang="scss">
    .permissions-grid-parent {
        flex: 1;
        min-height: 200px;
        overflow: hidden;
    }

    .permissions-grid-content {
        width: 100%;
        height: 100%;

    }
</style>
<script>
  import ProfileView from "../../../components/profile/profile.vue";
  export default {
    components: {
      "profile-view": ProfileView
    },
    props: ["wflName"],
    mounted() {
      $(window).resize(() => {
        if (this.$(this.$refs.permGrid.$refs.profileTreeList).data("kendoTreeList")) {
          this.$(this.$refs.permGrid.$refs.profileTreeList).data("kendoTreeList").resize();
        }
      });
    }
  }
</script>
