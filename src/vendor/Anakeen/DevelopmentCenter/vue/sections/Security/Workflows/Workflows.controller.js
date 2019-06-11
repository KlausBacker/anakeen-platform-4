import List from "devComponents/SSList/SSList.vue";
import RouterTabs from "devComponents/RouterTabs/RouterTabs.vue";
export default {
  components: {
    "ss-list": List,
    RouterTabs,
    "security-wfl-rights": resolve =>
      import("./Rights/Rights.vue").then(module => resolve(module.default))
  },
  computed: {
    listContent() {
      return this.wflList.filter(item => this.alreadyClicked(item));
    }
  },
  props: ["wflName", "wflSection"],
  data() {
    return {
      selectedWorkflow: this.wflName,
      wflList: [],
      alreadyVisited: {},
      tabs: [
        {
          name: "Security::Workflows::Rights",
          label: "Smart Element Rights",
          component: "security-wfl-rights",
          url: "rights"
        }
      ]
    };
  },
  methods: {
    onTabsMounted(wflName) {
      if (this.wflName === wflName) {
        this.$refs[this.wflName][0].setSelectedTab(tab => {
          return tab.url === this.wflSection;
        });
        this.$emit(`${wflName}-ready`);
      }
    },
    onTabSelected() {
      this.onChildNavigate();
    },
    onItemClicked(item) {
      this.selectedWorkflow = item.name;

      this.$nextTick(() => {
        this.onChildNavigate();
      });
    },
    getRoute() {
      if (!this.selectedWorkflow) {
        return Promise.resolve([]);
      }
      const ssName = {
        name: this.selectedWorkflow,
        label: this.selectedWorkflow,
        url: this.selectedWorkflow
      };
      const result = [ssName];
      return new Promise(resolve => {
        if (this.$refs[this.selectedWorkflow]) {
          const selTab = this.$refs[this.selectedWorkflow][0].selectedTab;
          result.push(selTab);
          resolve(result);
        } else {
          this.$once(`${this.selectedWorkflow}-ready`, () => {
            const selTab = this.$refs[this.selectedWorkflow][0].selectedTab;
            result.push(selTab);
            resolve(result);
          });
        }
      });
    },
    onListReady(data) {
      this.wflList = data;
    },
    alreadyClicked(item) {
      if (item && item.name === this.selectedWorkflow) {
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
