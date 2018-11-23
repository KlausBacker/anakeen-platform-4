<template>
    <div class="all-routes-tabs">
        <router-tabs class="all-routes-section" :items="items"></router-tabs>
    </div>
</template>

<script>
  export default {
    name: "allRoutesContent",
    data() {
      return {
        contentVisible: false,
        items: [
          {
            name: "routes",
            label: "Routes",
          },
          {
            name: "middlewares",
            label: "Middlewares",
          }
        ]
      }
    }
  }
</script>

<style lang="scss" scoped>
    .all-routes-tabs {
        height: 100%;
        width: 100%;
        min-height: 0;
        display: flex;
    }
    .all-routes-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;

        .all-routes-content {
            min-height: 0;
            display: flex;
            flex: 1;
            padding: 1rem;
            border: 1px solid rgba(33, 37, 41, .125);
            border-radius: .25rem;
        }
    }
</style>