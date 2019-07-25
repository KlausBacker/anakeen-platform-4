import RouterTabs from "devComponents/RouterTabs/RouterTabs.vue";
export default {
  components: {
    "ss-list": resolve => import("devComponents/SSList/SSList.vue").then(module => resolve(module.default)),
    RouterTabs,
    "security-ss-infos": resolve => import("./subsections/Infos/Infos.vue").then(module => resolve(module.default)),
    "security-ss-access": resolve =>
      import("./subsections/Structures/Structures.vue").then(module => resolve(module.default)),
    "security-ss-element-access": resolve =>
      import("./subsections/Elements/Elements.vue").then(module => resolve(module.default)),
    "security-ss-field-access": resolve =>
      import("./subsections/Fields/Fields.vue").then(module => resolve(module.default))
  },
  props: ["ssName", "ssSection"],
  computed: {
    listContent() {
      return this.ssList.filter(item => this.alreadyClicked(item));
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
          name: "Security::SmartStructures::Infos",
          label: "Informations",
          component: "security-ss-infos",
          url: "infos"
        },
        {
          name: "Security::SmartStructures::Structures",
          label: "Structure Access",
          component: "security-ss-access",
          url: "structureProfile"
        },
        {
          name: "Security::SmartStructures::Elements",
          label: "Default Element Access",
          component: "security-ss-element-access",
          url: "elementsProfile"
        },
        {
          name: "Security::SmartStructures::Fields",
          label: "Default Fields Access",
          component: "security-ss-field-access",
          url: "fields"
        }
      ]
    };
  },
  methods: {
    onTabsMounted(ssName) {
      if (this.ssName === ssName) {
        this.$refs[this.ssName][0].setSelectedTab(tab => {
          return tab.url === this.ssSection;
        });
      }
    },
    onSubComponentMounted(ssName, tabName) {
      this.$emit(`${ssName}-${tabName}-ready`);
    },
    onTabSelected() {
      this.onChildNavigate();
    },
    onItemClicked(tab) {
      this.selectedStructure = tab.name;

      this.$nextTick(() => {
        this.onChildNavigate();
      });
    },
    getRoute() {
      if (!this.selectedStructure) {
        return Promise.resolve([]);
      }
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
    onListReady(data) {
      this.ssList = data;
    },
    alreadyClicked(item) {
      if (item && item.name === this.selectedStructure) {
        this.alreadyVisited[item.name] = true;
      }
      return item && this.alreadyVisited[item.name];
    },
    onChildNavigate() {
      this.getRoute().then(route => {
        this.$emit("navigate", route);
      });
    }
  }
};
