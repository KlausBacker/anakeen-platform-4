<template>
  <div class="wfl-parent-section">
    <div class="wfl-ss-list-empty" v-show="wflIsEmpty">
      <span class="k-icon k-i-folder-open wfl-empty-icon"></span>
      <span class="wfl-empty-text"
        >There are currently no Workflows associated with a Smart Structure
        ...</span
      >
    </div>
    <ss-list
      v-show="!wflIsEmpty"
      ref="wflSSList"
      position="left"
      :filter="{ placeholder: 'Search a workflow' }"
      listUrl="/api/v2/devel/workflow/structures/<type>/"
      :selected="selectedWfl"
      @item-clicked="onItemClicked"
      @list-ready="onListReady"
    >
    </ss-list>
    <div class="workflow-content">
      <router-tabs
        :ref="listItem.id"
        v-for="listItem in listContent"
        @hook:mounted="onTabsMounted(listItem.id)"
        @tab-selected="onTabSelected"
        :key="listItem.id"
        v-show="listItem && listItem.id === selectedWfl"
        :tabs="tabs"
      >
        <template v-slot="slotProps">
          <component
            :ref="`${listItem.id}-${slotProps.tab.name}`"
            @navigate="onChildNavigate"
            @hook:mounted="
              onSubComponentMounted(listItem.id, slotProps.tab.name)
            "
            :is="slotProps.tab.component"
            :wflName="listItem.id"
          ></component>
        </template>
      </router-tabs>
      <div class="workflow-empty" v-if="!selectedWfl">
        <div class="empty-content">
          <span class="k-icon k-i-folder-open wfl-empty-icon"></span>
          <span class="wfl-empty-text">Select a workflow</span>
        </div>
      </div>
    </div>
  </div>
</template>
<style lang="scss">
.wfl-parent-section {
  min-height: 0;
  padding: 2rem;
  flex: 1;
  display: flex;
  background: white;
}

.workflow-content {
  border-radius: 0.25rem;
  flex: 1;
  display: flex;
}

.wfl-ss-list-empty {
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
}
.wfl-empty-icon {
  flex: 1;
  font-size: 20rem;
}
.wfl-empty-text {
  flex: 1;
  font-size: 24px;
}

.workflow-empty {
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

  .wfl-empty-icon {
    flex: 1;
    font-size: 20rem;
  }
  .wfl-empty-text {
    flex: 1;
    font-size: 2rem;
  }
}
</style>
<script>
import RouterTabs from "devComponents/RouterTabs/RouterTabs.vue";
import SSList from "devComponents/SSList/SSList.vue";
export default {
  name: "Wfl",
  components: {
    "ss-list": SSList,
    "router-tabs": RouterTabs,
    "wfl-infos": resolve =>
            import("./Infos/Infos.vue").then(module => resolve(module.default)),
    "wfl-steps": resolve =>
            import("./Steps/Steps.vue").then(module => resolve(module.default)),
    "wfl-transitions": resolve =>
            import("./Transitions/Transitions.vue").then(module =>
                    resolve(module.default)
            ),
    "wfl-permissions": resolve =>
            import("./Permissions/Permissions.vue").then(module =>
                    resolve(module.default)
            )
  },
  props: ["wflName", "wflType"],
  computed: {
    listContent() {
      return this.wflList.filter(item => this.alreadyClicked(item));
    }
  },
  watch: {
    wflName(newValue) {
      this.selectedWfl = newValue;
    },
    wflType(newValue) {
      if (this.$refs[this.selectedWfl]) {
        this.$refs[this.selectedWfl][0].setSelectedTab(tab => {
          return tab.url === newValue;
        });
      }
    }
  },
  data() {
    return {
      selectedWfl: this.wflName,
      wflIsEmpty: true,
      wflList: [],
      alreadyVisited: {},
      subComponentsRefs: {},
      tabs: [
        {
          name: "Wfl::infos",
          label: "Informations",
          url: "infos",
          component: "wfl-infos"
        },
        {
          name: "Wfl::steps",
          label: "Steps",
          url: "steps",
          component: "wfl-steps"
        },
        {
          name: "Wfl::transitions",
          label: "Transitions",
          url: "transitions",
          component: "wfl-transitions"
        },
        {
          name: "Wfl::permissions",
          label: "Permissions",
          url: "permissions",
          component: "wfl-permissions"
        }
      ]
    };
  },
  mounted() {
    this.$refs.wflSSList.dataSource.bind("change", e => {
      e.items && e.items.length === 0
        ? (this.wflIsEmpty = true)
        : (this.wflIsEmpty = false);
    });
  },
  methods: {
    onTabsMounted(wflName) {
      if (this.wflName === wflName) {
        this.$refs[this.wflName][0].setSelectedTab(tab => {
          return tab.url === this.wflType;
        });
      }
    },
    onSubComponentMounted(wflName, tabName) {
      this.$emit(`${wflName}-${tabName}-ready`);
    },
    onItemClicked(item) {
      this.selectedWfl = item.id;

      this.$nextTick(() => {
        this.onChildNavigate();
      });
    },
    onListReady(data) {
      this.wflList = data;
    },
    alreadyClicked(item) {
      if (item && item.id === this.selectedWfl) {
        this.alreadyVisited[item.id] = true;
      }
      return item && this.alreadyVisited[item.id];
    },
    onTabSelected() {
      this.onChildNavigate();
    },
    getRoute() {
      const wflName = {
        name: this.selectedWfl,
        label: this.selectedWfl,
        url: this.selectedWfl
      };
      console.log(this.$refs, this.selectedWfl);
      const selTab = this.$refs[this.selectedWfl][0].selectedTab;
      const result = [wflName, selTab];
      const ref = `${this.selectedWfl}-${selTab.name}`;

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
};</script
>œ
