<template>
    <div class="parameters-parent">
        <router-tabs :items="items"></router-tabs>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Parameters.scss";
</style>

<script>
  export default {
    data() {
      return {
        items: [
          {
            name: "SmartStructures::parameters::parameterFields",
            label: "Fields"
          },
          {
            name: "SmartStructures::parameters::parameterValues",
            label: "Values"
          }
        ]
      }
    }
  }
</script>