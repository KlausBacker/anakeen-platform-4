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
            label: "Structure Access"
          },
          {
            name: "Security::SmartStructures::Elements",
            label: "Element Access"
          },
          {
            name: "Security::SmartStructures::Fields",
            label: "Fields Access"
          }
        ]
      }
    }
  }
</script>

<style scoped>

</style>