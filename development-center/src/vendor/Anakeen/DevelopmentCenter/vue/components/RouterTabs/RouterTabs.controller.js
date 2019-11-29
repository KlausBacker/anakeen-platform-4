import AnkSETabs from "@anakeen/user-interfaces/components/lib/AnkSmartElementTabs.esm";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab.esm";

export default {
  name: "router-tabs",
  components: {
    "ank-se-tabs": AnkSETabs,
    AnkTab
  },
  props: {
    tabs: {
      type: Array,
      default: () => []
    }
  },
  watch: {
    selectedTab(newValue) {
      this.onSelectedTab(newValue.name);
    }
  },
  computed: {
    selected: {
      get() {
        return this.selectedTab ? this.selectedTab.name : "";
      },
      set(newValue) {
        if (newValue) {
          const result = this.tabs.filter(t => t.name === newValue);
          if (result && result.length) {
            this.selectedTab = result[0];
          }
        }
      }
    }
  },
  data() {
    return {
      selectedTab: this.tabs.length ? this.tabs[0] : null
    };
  },
  methods: {
    onSelectedTab() {
      this.$emit("tab-selected", this.selectedTab);
    },
    setSelectedTab(selectTab) {
      if (typeof selectTab === "function") {
        const selectedTab = this.tabs.filter(t => selectTab(t));
        if (selectedTab && selectedTab.length) {
          this.selectedTab = selectedTab[0];
        }
      } else {
        this.selected = selectTab;
      }
    }
  }
};
