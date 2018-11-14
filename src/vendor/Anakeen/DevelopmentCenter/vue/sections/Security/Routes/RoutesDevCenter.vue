<template>
    <div class="routes-parent">
        <router-tabs :items="items"></router-tabs>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./RoutesDevCenter.scss";
</style>

<script>
    export default {
      data() {
        return {
          items: [
            {
              name: "Security::Routes::RoutesAcl",
              label: "Access Control"
            },
            {
              name: "Security::Routes::RoutesPermissions",
              label: "Permissions"
            }
          ]
        }
      }
    }
</script>