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
            name: "SmartStructures::fields",
            label: "Fields"
          },
          {
            name: "SmartStructures::parameters",
            label: "Parameters"
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
        flex: 1;

        .ss-content {
            display: flex;
            flex: 1;
            padding: 1rem;
            border: 1px solid rgba(33, 37, 41, .125);
            border-radius: .25rem;
            min-height: 0;
            flex-direction: column;
        }

    }
</style>