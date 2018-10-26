<template>
    <router-tabs :items="items"></router-tabs>
</template>

<script>
  export default {
    name: "SmartStructuresContent",
    data() {
      return {
        items: [
          {
            name: "Security::SmartStructures::Infos",
            label: "Informations"
          },
          {
            name: "Security::SmartStructures::Structures",
            label: "Smart Structures"
          },
          {
            name: "Security::SmartStructures::Elements",
            label: "Smart Elements"
          },
          {
            name: "Security::SmartStructures::Fields",
            label: "Smart Fields"
          }
        ]
      }
    }
  }
</script>

<style scoped>

</style>