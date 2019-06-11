<template>
    <div class="parameters-parent">
        <router-tabs ref="tabsComponent" :tabs="tabs" @tab-selected="onTabSelected">
            <template v-slot="slotProps">
                <component :is="slotProps.tab.component" :ssName="ssName"></component>
            </template>
        </router-tabs>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Parameters.scss";
</style>

<script>
  import RouterTabs from "devComponents/RouterTabs/RouterTabs.vue";
  export default {
    components: {
      RouterTabs,
      "ss-parameters-fields": resolve => import("./ParametersFields").then(module => resolve(module.default)),
      "ss-parameters-values": resolve => import("./ParametersValues").then(module => resolve(module.default)),
      "ss-parameters-default-values": resolve => import("./ParametersDefaultValue").then(module => resolve(module.default)),
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
            name: "SmartStructures::parameters::parametersFields",
            label: "Parameters Fields",
            url: "fields",
            component: "ss-parameters-fields",
          },
          {
            name: "SmartStructures::parameters::parametersValues",
            label: "Parameters Values",
            url: "values",
            component: "ss-parameters-values",
          },
          {
            name: "SmartStructures::parameters::defaultParamValues",
            label: "Default Parameters Values",
            url: "defaults",
            component: "ss-parameters-default-values",
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