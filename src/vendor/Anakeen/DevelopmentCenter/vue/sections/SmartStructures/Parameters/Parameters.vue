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
            name: "SmartStructures::parameters::parametersValues",
            label: "Parameters Fields"
          },
          {
            name: "SmartStructures::parameters::defaultParamFields",
            label: "Parameters Values"
          }
        ]
      }
    }
  }
</script>