<template>
    <div class="ui-parent-section">
        <ss-list
                 position="left"
                 :selected="selectedStructure"
                 @item-clicked="onItemClicked"
                 @list-ready="onListReady"
        >
        </ss-list>
        <div class="ui-content">
            <router-tabs :ref="listItem.name" v-for="(listItem, index) in listContent" :key="index" @hook:mounted="onTabsMounted(listItem.name)" @tab-selected="onTabSelected" v-show="listItem && listItem.name === selectedStructure" :tabs="tabs">
                <template v-slot="slotProps">
                    <component :is="slotProps.tab.component" :ssName="listItem.name"></component>
                </template>
            </router-tabs>
            <div class="ui-empty" v-if="!selectedStructure">
                <div class="empty-content">
                    <span class="k-icon k-i-folder-open ui-empty-icon"></span>
                    <span class="ui-empty-text">Select a structure</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import RouterTabs from "devComponents/RouterTabs/RouterTabs.vue";
    export default {
      components: {
        "ss-list": resolve =>
          import("devComponents/SSList/SSList.vue").then(module =>
            resolve(module.default)
          ),
        RouterTabs,
        "ui-infos": resolve =>
          import("./Infos/Infos.vue").then(module => resolve(module.default)),
        "ui-views": resolve =>
          import("./ViewsConfiguration/ViewsConf.vue").then(module => resolve(module.default)),
        "ui-control": resolve =>
          import("./ControlConfiguration/ControlConf.vue").then(module =>
            resolve(module.default)
          ),
        "ui-masks": resolve =>
          import("./Masks/Masks.vue").then(module =>
            resolve(module.default)
          )
      },
      props: ["ssName", "uiSection"],
      computed: {
        listContent() {
          return this.ssList.filter(item => this.alreadyClicked(item))
        }
      },
      data() {
        return {
          selectedStructure: this.ssName,
          ssList: [],
          alreadyVisited: {},
          tabs: [
            {
              name: "Ui::infos",
              label: "Informations",
              component: "ui-infos",
              url: "infos"
            },
            {
              name: "Ui::views",
              label: "View Configuration",
              component: "ui-views",
              url: "views"
            },
            {
              name: "Ui::control",
              label: "Control Configuration",
              component: "ui-control",
              url: "control"
            },
            {
              name: "Ui::masks",
              label: "Masks",
              component: "ui-masks",
              url: "masks"
            }
          ]
        }
      },
      methods: {
        onTabsMounted(ssName) {
          if (this.ssName === ssName) {
            this.$refs[this.ssName][0].setSelectedTab((tab) => {
              return tab.url === this.uiSection;
            })
          }
        },
        onItemClicked(tab) {
          this.selectedStructure = tab.name;

          this.$nextTick(() => {
            this.onChildNavigate();
          })
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
          return Promise.resolve(result);
        },
        onChildNavigate() {
          this.getRoute().then((route) => {
            this.$emit("navigate", route);
          });
        }
      }
    }
</script>

<style lang="scss">
    .ui-parent-section {
        min-height: 0;
        padding: 2rem;
        flex: 1;
        display: flex;

        .ui-content {
            flex: 1;
            display: flex;
            border: 1px solid #d2d2d2;
            border-radius: 0.25rem;

            .ui-empty {
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

                .ui-empty-icon {
                    flex: 1;
                    font-size: 20rem;
                }
                .ui-empty-text {
                    flex: 1;
                    font-size: 2rem;
                }
            }
        }
    }
</style>