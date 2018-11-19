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
        min-height: 0;
        display: flex;
    }
    .wfl-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;

        .wfl-content {
            min-height: 0;
            display: flex;
            flex: 1;
            padding: 1rem;
            border: 1px solid rgba(33, 37, 41, .125);
            border-radius: .25rem;
        }
    }
</style>