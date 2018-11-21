<template>
    <router-tabs :items="items"></router-tabs>
</template>

<script>
  export default {
    name: "WorkflowsContent",
    data() {
      return {
        items: [
          {
            name: "Security::Workflows::Rights",
            label: "Rights"
          },
          {
            name: "Security::Workflows::Access",
            label: "Field Access"
          }
        ]
      }
    }
  }
</script>

<style scoped>

</style>