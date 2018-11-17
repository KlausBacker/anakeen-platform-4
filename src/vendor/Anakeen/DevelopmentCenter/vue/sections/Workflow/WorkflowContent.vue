<template>
    <div class="wfl-tabs">
        <router-tabs class="wfl-section" :items="items"></router-tabs>
    </div>
</template>

<script>
  export default {
    name: "WflContent",
    data() {
      return {
        contentVisible: false,
        items: [
          {
            name: "Wfl::infos",
            label: "Informations",
            props: true
          },
          {
            name: "Wfl::steps",
            label: "Steps",
            props: true
          },
          {
            name: "Wfl::transitions",
            label: "Transitions",
            props: true
          },
          {
            name: "Wfl::permissions",
            label: "Permissions",
            props: true
          }
        ]
      }
    }
  }
</script>

<style lang="scss" scoped>
    .wfl-tabs {
        height: 100%;
        width: 100%;
    }
    .wfl-section {
        display: flex;
        flex-direction: column;
        height: 100%;

        .wfl-content {
            display: flex;
            flex: 1;
            padding: 1rem;
            border: 1px solid rgba(33, 37, 41, .125);
            border-radius: .25rem;
        }
    }
</style>