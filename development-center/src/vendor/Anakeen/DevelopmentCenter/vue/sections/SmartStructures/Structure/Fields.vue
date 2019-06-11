<template>
    <div class="fields-parent">
        <router-tabs ref="tabsComponent" :tabs="tabs" @tab-selected="onTabSelected">
            <template v-slot="slotProps">
                <component :is="slotProps.tab.component" :ssName="ssName"></component>
            </template>
        </router-tabs>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Fields.scss";
</style>

<script>
  import RouterTabs from "devComponents/RouterTabs/RouterTabs.vue";
  export default {
    components: {
      RouterTabs,
      "ss-structure": resolve => import("./Structure.vue").then(module => resolve(module.default)),
      "ss-default-values": resolve => import("./DefaultValues.vue").then(module => resolve(module.default))
    },
    props: ["ssName", "ssDetails"],
    mounted() {
      this.$refs.tabsComponent.setSelectedTab((tab) => {
        return tab.url === this.ssDetails;
      })
    },
    data() {
      return {
        tabs: [
          {
            name: "SmartStructures::fields::structure",
            label: "Smart Fields",
            url: "structure",
            component: "ss-structure"
          },
          {
            name: "SmartStructures::fields::defaults",
            label: "Default Values",
            url: "defaults",
            component: "ss-default-values"
          }
        ]
      }
    },
    methods: {
      onTabSelected() {
        this.getRoute().then((route) => {
          this.$emit("navigate", route);
        })
      },
      getRoute() {
        return Promise.resolve(this.$refs.tabsComponent.selectedTab);
      }
    }
  }
</script>