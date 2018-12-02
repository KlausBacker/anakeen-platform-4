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
            label: "Smart Element Rights"
          }
        ]
      }
    }
  }
</script>

<style scoped>

</style>