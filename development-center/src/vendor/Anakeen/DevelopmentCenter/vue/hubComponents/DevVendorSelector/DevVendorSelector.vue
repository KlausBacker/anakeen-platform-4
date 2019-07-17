<template>
    <div class="header-breadcrumb">
            <span v-for="(routeItem, index) in routesSections" :key="index">
                <span :class="{ 'header-breadcrumb-root-item': index === 0, 'header-breacrumb-item': true }">
                    {{getRouteLabel(routeItem)}}
                </span>
                <span class="header-breadcrumb-separator" v-if="index !== routesSections.length - 1"> > </span>
            </span>
    </div>
</template>

<style lang="scss">
    .header-breadcrumb-root-item {
        font-size: 1.33rem;
        color: white;
        font-weight: bold;
    }

    .header-breadcrumb-separator {
        margin-left: .5rem;
        margin-right: .5rem;
    }
</style>
<script>
  import HubElement from "@anakeen/hub-components/components/lib/HubElement";
  export default {
    name: "ank-dev-breadcrumb",
    extends: HubElement,
    computed: {
      routesSections() {
        return [
          {
            name: "Development Center"
          }
        ]
      }
    },
    methods: {
      getRouteLabel(route) {
        if (route.meta && route.meta.label) {
          let title = route.name;
          if (typeof route.meta.label === "function") {
            title = route.meta.label.call(null, this.$route);
            if (!title) {
              return route.name;
            }
          } else {
            title = route.meta.label.trim();
          }
          const regex = /:[a-zA-Z0-9]+/g;
          const matches = title.match(regex) || [];
          matches.forEach(m => {
            const paramName = m.replace(":", "");
            title = title.replace(m, this.$route.params[paramName]);
          });
          return title;
        }
        return route.name;
      }
    }
  }
</script>

<style scoped>

</style>