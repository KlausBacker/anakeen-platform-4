<template>
    <router-tabs class="ss-section" :items="items"></router-tabs>
</template>

<script>
  export default {
    name: "SSContent",
    data() {
      return {
        items: [
          {
            name: "SmartStructures::infos",
            label: "Infos"
          },
          {
            name: "SmartStructures::structure",
            label: "Structure"
          },
          {
            name: "SmartStructures::parameters::parameterValues",
            label: "Parameters"
          },
          {
            name: "SmartStructures::defaults",
            label: "Defaults"
          },
          {
            name: "SmartStructures::others",
            label: "Others"
          }
        ]
      }
    }
  }
</script>

<style lang="scss" scoped>
    .ss-section {
        display: flex;
        flex-direction: column;

        .ss-content {
            display: flex;
            flex: 1;
            padding: 1rem;
            border: 1px solid rgba(33, 37, 41, .125);
            border-radius: .25rem;
        }

    }
</style>