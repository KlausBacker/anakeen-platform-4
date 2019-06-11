<template>
  <div class="smart-structure-section">
    <ss-list
      position="left"
      :selected="selectedStructure"
      @item-clicked="onItemClicked"
      @list-ready="onListReady"
    >
    </ss-list>
    <div class="smart-structure-content">
      <router-tabs
        :ref="listItem.name"
        v-for="listItem in listContent"
        @hook:mounted="onTabsMounted(listItem.name)"
        @tab-selected="onTabSelected"
        :key="listItem.name"
        v-show="listItem && listItem.name === selectedStructure"
        :tabs="tabs"
      >
        <template v-slot="slotProps">
          <component
            :ref="`${listItem.name}-${slotProps.tab.name}`"
            @navigate="onChildNavigate"
            @hook:mounted="
              onSubComponentMounted(listItem.name, slotProps.tab.name)
            "
            :is="slotProps.tab.component"
            :ssName="listItem.name"
            :ssDetails="ssDetails"
          ></component>
        </template>
      </router-tabs>
      <div class="smart-structure-empty" v-if="!selectedStructure">
        <div class="empty-content">
          <span class="k-icon k-i-folder-open ss-empty-icon"></span>
          <span class="ss-empty-text">Select a structure</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.smart-structure-section {
  flex: 1;
  display: flex;
  padding: 2rem;
  background: white;
  min-height: 0;

  .smart-structure-content {
    border-radius: 0.25rem;
    flex: 1;
    display: flex;
  }

  .smart-structure-details {
    padding: 0.5rem;
    border: 1px solid #d2d2d2;
    border-radius: 0.25rem;
    flex: 1;

    .smart-structure-detail {
      display: flex;
    }
  }

  .smart-structure-empty {
    padding: 0.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    border: 1px solid #d2d2d2;
    border-radius: 0.25rem;
    align-items: center;
    justify-content: center;
    color: #848484;
    min-height: 0;
    overflow: hidden;

    .empty-content {
      display: flex;
      flex-direction: column;
    }

    .ss-empty-icon {
      flex: 1;
      font-size: 20rem;
    }
    .ss-empty-text {
      flex: 1;
      font-size: 2rem;
    }
  }
}
</style>

<script>
import RouterTabs from "devComponents/RouterTabs/RouterTabs.vue";
export default {
  components: {
    "ss-list": resolve =>
      import("devComponents/SSList/SSList.vue").then(module =>
        resolve(module.default)
      ),
    RouterTabs,
    "ss-infos": resolve =>
      import("./Infos/Infos.vue").then(module => resolve(module.default)),
    "ss-fields": resolve =>
      import("./Structure/Fields.vue").then(module => resolve(module.default)),
    "ss-parameters": resolve =>
      import("./Parameters/Parameters.vue").then(module =>
        resolve(module.default)
      )
  },
  props: ["ssName", "ssType", "ssDetails"],
  computed: {
    listContent() {
      return this.ssList.filter(item => this.alreadyClicked(item));
    }
  },
  watch: {
    ssName(newValue) {
      this.selectedStructure = newValue;
    },
    ssType(newValue) {
      if (this.$refs[this.selectedStructure]) {
        this.$refs[this.selectedStructure][0].setSelectedTab(tab => {
          return tab.url === newValue;
        });
      }
    }
  },
  data() {
    return {
      selectedStructure: this.ssName,
      ssList: [],
      alreadyVisited: {},
      subComponentsRefs: {},
      tabs: [
        {
          name: "SmartStructures::infos",
          label: "Infos",
          url: "infos",
          component: "ss-infos"
        },
        {
          name: "SmartStructures::fields",
          label: "Fields",
          url: "fields",
          component: "ss-fields"
        },
        {
          name: "SmartStructures::parameters",
          url: "parameters",
          label: "Parameters",
          component: "ss-parameters"
        }
      ]
    };
  },
  methods: {
    onTabsMounted(ssName) {
      if (this.ssName === ssName) {
        this.$refs[this.ssName][0].setSelectedTab(tab => {
          return tab.url === this.ssType;
        });
      }
    },
    onSubComponentMounted(ssName, tabName) {
      this.$emit(`${ssName}-${tabName}-ready`);
    },
    onItemClicked(tab) {
      this.selectedStructure = tab.name;

      this.$nextTick(() => {
        this.onChildNavigate();
      });
    },
    onListReady(data) {
      this.ssList = data;
    },
    alreadyClicked(item) {
      if (item && item.name === this.selectedStructure) {
        this.alreadyVisited[item.name] = true;
      }
      return item && this.alreadyVisited[item.name];
    },
    onTabSelected() {
      this.onChildNavigate();
    },
    getRoute() {
      const ssName = {
        name: this.selectedStructure,
        label: this.selectedStructure,
        url: this.selectedStructure
      };
      const selTab = this.$refs[this.selectedStructure][0].selectedTab;
      const result = [ssName, selTab];
      const ref = `${this.selectedStructure}-${selTab.name}`;

      if (this.$refs[ref]) {
        this.subComponentsRefs[ref] = Promise.resolve(this.$refs[ref]);
      } else {
        this.subComponentsRefs[ref] = new Promise(resolve => {
          this.$once(`${ref}-ready`, () => {
            resolve(this.$refs[ref]);
          });
        });
      }
      return this.subComponentsRefs[ref].then(component => {
        if (component && component.length && component[0].getRoute) {
          return component[0].getRoute().then(childRoute => {
            result.push(childRoute);
            return result;
          });
        } else {
          return result;
        }
      });
    },
    onChildNavigate() {
      this.getRoute().then(route => {
        this.$emit("navigate", route);
      });
    }
  }
};
</script>
