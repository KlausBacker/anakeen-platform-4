<template>
    <div class="fields-parent">
        <router-tabs :items="items"></router-tabs>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Fields.scss";
</style>

<script>
  export default {
    data() {
      return {
        items: [
          {
            name: "SmartStructures::fields::structure",
            label: "Default Fields Values"
          },
          {
            name: "SmartStructures::fields::defaults",
            label: "Default Values"
          }
        ]
      }
    }
  }
</script>