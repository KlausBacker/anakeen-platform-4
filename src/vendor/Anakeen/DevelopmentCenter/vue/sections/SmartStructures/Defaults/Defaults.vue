<template>
    <div class="defaults-parent">
        <router-tabs :items="items"></router-tabs>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Defaults.scss";
</style>

<script>
  export default {
    data() {
      return {
        items: [
          {
            name: "SmartStructures::defaults::defaultFields",
            label: "Default Values"
          },
          {
            name: "SmartStructures::defaults::defaultParamFields",
            label: "Default Parameters"
          }
        ]
      }
    }
  }
</script>