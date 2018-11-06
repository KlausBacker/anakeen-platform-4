<template>
    <router-tabs class="ui-section" :items="items"></router-tabs>
</template>

<script>
  export default {
    name: "UiContent",
    data() {
      return {
        items: [
          {
            name: "Ui::infos",
            label: "Informations"
          },
          {
            name: "Ui::views",
            label: "View Configuration"
          },
          {
            name: "Ui::control",
            label: "Control Configuration"
          },
          {
            name: "Ui::masks",
            label: "Masks"
          }
        ]
      }
    }
  }
</script>

<style lang="scss" scoped>
    .ui-section {
        display: flex;
        flex-direction: column;

        .ui-content {
            display: flex;
            flex: 1;
            padding: 1rem;
            border: 1px solid rgba(33, 37, 41, .125);
            border-radius: .25rem;
        }
    }
</style>