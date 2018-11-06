<template>
    <div class="security-parent">
        <router-tabs class="security-content" :items="items"></router-tabs>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./SecurityDevCenter.scss";
</style>

<script>
    export default {
      data() {
        return {
          items: [
            {
              name: "Security::Routes",
              label: "Routes"
            },
            {
              name: "Security::SmartStructures",
              label: "Smart Structures"
            },
            {
              name: "Security::Profiles",
              label: "Profiles"
            },
            {
              name: "Security::Roles",
              label: "Roles"
            },
            {
              name: "Security::Profile::Access",
              label: "Profile Access Control"
            }
          ]
        }
      }
    }
</script>