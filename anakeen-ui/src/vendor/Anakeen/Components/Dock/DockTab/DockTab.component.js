export default {
  name: "ank-dock-tab",

  props: {
    // Define if the tab should be selected at dock loading
    selectedTab: {
      type: Boolean,
      default: false
    },

    // Define if the tab is selectable to display its content
    selectableTab: {
      type: Boolean,
      default: true
    },

    // Define if the tab is a header tab of the dock
    headerTab: {
      type: Boolean,
      default: false
    },

    // Define if the tab is a footer tab of the dock
    footerTab: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      id: "",
      compact: "",
      expanded: "",
      content: ""
    };
  },

  methods: {
    // Send the tab to the parent dock with an event
    emitTab() {
      this.$dockEventBus.$emit("tabLoaded", {
        id: this.id,
        compact: this.compact,
        expanded: this.expanded,
        content: this.content,
        selected: this.selectedTab,
        selectable: this.selectableTab
      });
    },

    // Send the tab as a header tab to the parent dock with an event
    emitHeaderTab() {
      this.$dockEventBus.$emit("headerTabLoaded", {
        id: this.id,
        compact: this.compact,
        expanded: this.expanded,
        content: this.content,
        selected: this.selectedTab,
        selectable: this.selectableTab
      });
    },

    // Send the tab as a header tab to the parent dock with an event
    emitFooterTab() {
      this.$dockEventBus.$emit("footerTabLoaded", {
        id: this.id,
        compact: this.compact,
        expanded: this.expanded,
        content: this.content,
        selected: this.selected,
        selectable: this.selectableTab
      });
    }
  },

  mounted() {
    this.id = this._uid;
    this.compact = this.$("#compactFragment").html();
    this.expanded = this.$("#expandedFragment").html();
    this.content = this.$("#contentFragment").html();

    this.$("#compactFragment").remove();
    this.$("#expandedFragment").remove();
    this.$("#contentFragment").remove();

    if (this.compact || this.expanded || this.content) {
      if (this.headerTab) {
        this.emitHeaderTab();
      } else if (this.footerTab) {
        this.emitFooterTab();
      } else {
        this.emitTab();
      }
    }
  }
};
