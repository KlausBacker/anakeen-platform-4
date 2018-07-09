import AnkMixin from "../AnkVueComponentMixin/AnkVueComponentMixin";

export default {
  name: "ank-dock",

  mixins: [AnkMixin],

  props: {
    // Position of the dock, must be left, right, top or bottom
    position: {
      type: String,
      validator(value) {
        return ["left", "right", "top", "bottom"].indexOf(value) !== -1;
      },

      default: "left"
    },

    // Define if the dock is expandable or not
    expandable: {
      type: Boolean,
      default: true
    },

    // Define the initial format of the dock (compact or expanded)
    expanded: {
      type: Boolean,
      default: false
    },

    // Size of the dock when it is not expanded
    compactSize: {
      type: String,
      default: "4.5rem"
    },

    // Size of the dock when it is expanded
    largeSize: {
      type: String,
      default: "10rem"
    },

    // Determine if the expanded dock should move the content or superpose it
    superposeDock: {
      type: Boolean,
      default: false
    },

    // Determine if the dock must collapse when a new tab is selected
    collapseOnTabSelection: {
      type: Boolean,
      default: true
    }
  },

  data() {
    return {
      tabs: [],
      headerTabs: [],
      footerTabs: [],
      expandedDock: false,
      selectedTab: "-1",
      size: "",
      loadedTabs: 0
    };
  },

  methods: {
    // Add a tab, in header, tabs or footer
    addTab(tab, area, position) {
      if (
        tab.hasOwnProperty("compact") &&
        tab.hasOwnProperty("expanded") &&
        tab.hasOwnProperty("content") &&
        tab.hasOwnProperty("selectable") &&
        tab.hasOwnProperty("selected")
      ) {
        if (area === "header") {
          this.privateScope.addHeaderTab(tab, position);
        } else if (area === "footer") {
          this.privateScope.addFooterTab(tab, position);
        } else {
          this.privateScope.addTabToDock(tab, position);
        }
      } else {
        console.error(
          "tab should have all required properties to be added " +
            "(compact, expanded, content, selectable, selected"
        );
      }
    },

    // Remove a tab with its area (header, tabs or footer) and its postion in this area
    removeTab(position, area) {
      if (area === "header") {
        if (this.headerTabs[position] !== undefined) {
          this.privateScope.removeTabWithId(this.headerTabs[position].id);
        }
      } else if (area === "footer") {
        if (this.footerTabs[position] !== undefined) {
          this.privateScope.removeTabWithId(this.footerTabs[position].id);
        }
      } else {
        if (this.tabs[position] !== undefined) {
          this.privateScope.removeTabWithId(this.tabs[position].id);
        }
      }
    },

    // Move a tab from one position to an other
    moveTab(actualPosition, newPosition, actualArea, newArea) {
      let actualTabsArea;
      if (actualArea === "header") {
        actualTabsArea = this.headerTabs;
      } else if (actualArea === "footer") {
        actualTabsArea = this.footerTabs;
      } else {
        actualTabsArea = this.tabs;
      }

      let newTabsArea;
      if (newArea === "header") {
        newTabsArea = this.headerTabs;
      } else if (newArea === "footer") {
        newTabsArea = this.footerTabs;
      } else {
        newTabsArea = this.tabs;
      }

      if (actualTabsArea[actualPosition]) {
        let notCanceled = this.$emitAnkEvent("beforeTabMove", {
          tab: actualTabsArea[actualPosition]
        });

        if (notCanceled) {
          let tab = actualTabsArea[actualPosition];
          actualTabsArea.splice(actualPosition, 1);
          newTabsArea.splice(newPosition, 0, tab);

          this.$emit("tabMoved", tab);
        } else {
          this.$emit("tabMoveCanceled");
        }
      }
    },

    // Toggle expansion of the dock
    toggleExpansion() {
      if (this.expandable) {
        this.expandedDock ? this.contract() : this.expand();
      }
    },

    // Expand the dock to its large width
    expand() {
      if (this.expandable) {
        let notCanceled = this.$emitAnkEvent("beforeDockExpansion");

        if (notCanceled) {
          this.expandedDock = true;
          this.size = this.largeSize;
          this.$emit("dockExpanded");
        } else {
          this.$emit("dockExpansionCanceled");
        }
      }
    },

    // Contract the dock to its compact width
    contract() {
      if (this.expandable) {
        let notCanceled = this.$emitAnkEvent("beforeDockContraction");

        if (notCanceled) {
          this.expandedDock = false;
          this.size = this.compactSize;
          this.$emit("dockContracted");
        } else {
          this.$emit("dockContractionCanceled");
        }
      }
    },

    // Select a tab, to display its content, with its area (header, tabs or footer) and its position in the area
    selectTab(position, area) {
      if (position === -1) {
        this.selectTabWithId(-1);
      } else {
        if (area === "header" && this.headerTabs[position]) {
          this.selectTabWithId(this.headerTabs[position].id);
        } else if (area === "footer" && this.footerTabs[position]) {
          this.selectTabWithId(this.footerTabs[position].id);
        } else if (this.tabs[position]) {
          this.selectTabWithId(this.tabs[position].id);
        }
      }
    },

    // Select a tab, to display its content, with its id
    selectTabWithId(tabId) {
      if (tabId === -1) {
        let allTabs = this.headerTabs.concat(this.tabs, this.footerTabs);
        let actualSelectedTab = allTabs.find(
          element => element.id === this.selectedTab
        );
        let notCanceled = this.$emitAnkEvent("beforeTabSelection", {
          actualTab: actualSelectedTab,
          newTab: {}
        });

        if (notCanceled) {
          this.selectedTab = -1;
          actualSelectedTab.selected = false;
          this.$emit("tabSelected", {});
        } else {
          this.$emit("tabSelectionCanceled");
        }
      } else {
        let allTabs = this.headerTabs.concat(this.tabs, this.footerTabs);
        let actualSelectedTab = allTabs.find(
          element => element.id === this.selectedTab
        );
        let newSelectedTab = allTabs.find(element => element.id === tabId);
        if (newSelectedTab !== undefined && newSelectedTab.selectable) {
          let notCanceled = this.$emitAnkEvent("beforeTabSelection", {
            actualTab: actualSelectedTab,
            newTab: newSelectedTab
          });

          if (notCanceled) {
            this.selectedTab = newSelectedTab.id;
            if (actualSelectedTab) {
              actualSelectedTab.selected = false;
            }

            newSelectedTab.selected = true;

            if (this.collapseOnTabSelection) {
              this.contract();
            }

            this.$emit("tabSelected", newSelectedTab);
          } else {
            this.$emit("tabSelectionCanceled");
          }
        }
      }
    }
  },

  computed: {
    // Determine if there is content to display (content in the selected tab)
    contentDisplayed() {
      let allTabs = this.headerTabs.concat(this.tabs, this.footerTabs);
      let tab = allTabs.find(element => element.id === this.selectedTab);
      if (tab) {
        return tab.content;
      } else {
        return false;
      }
    },

    // Calculated sizes of the different parts of the component depending of its position (for style attributes)
    expandedSizeStyle() {
      return "width: calc(" + this.largeSize + " - " + this.compactSize + ")";
    },

    dockSizeStyle() {
      if (this.position === "left" || this.position === "right") {
        return "width: " + this.size;
      }

      return "height: " + this.compactSize;
    },

    tabSizeStyle() {
      if (this.expandedDock) {
        return "width: " + this.largeSize;
      }

      return "width: " + this.compactSize;
    },

    buttonSizeStyle() {
      if (
        (this.position === "left" || this.position === "right") &&
        this.expandedDock
      ) {
        return "width: " + this.largeSize;
      }

      return "width: " + this.compactSize;
    },

    headerSizeStyle() {
      if (
        this.expandedDock ||
        this.position === "top" ||
        this.position === "bottom"
      ) {
        return "width: " + this.largeSize;
      }

      return "width: " + this.compactSize;
    },

    footerSizeStyle() {
      if (
        !this.expandedDock ||
        this.position === "top" ||
        this.position === "bottom"
      ) {
        return "width: " + this.compactSize;
      }

      return "width: " + this.largeSize;
    },

    contentMarginStyle() {
      let style = "";
      if (this.superposeDock) {
        style += "margin-" + this.position + ": " + this.compactSize + "; ";
      }

      if (!this.contentDisplayed) {
        style += "visibility: hidden; ";
      }

      return style;
    },

    superposeDockClass() {
      let classes = "";
      if (this.superposeDock) {
        classes += "superpose-dock ";
      }

      if (this.expandedDock) {
        classes += "expanded-dock ";
      }

      return classes;
    }
  },

  created() {
    // Private methods
    let _this = this;
    this.privateScope = {
      // Add a tab and its content (tab object, must contain all needed properties)
      addTabToDock(tab, position) {
        let notCanceled = _this.$emitAnkEvent("beforeTabAdd", {
          tab: tab
        });

        if (notCanceled) {
          position === undefined
            ? _this.tabs.push(tab)
            : _this.tabs.splice(position, 0, tab);
          if (tab.selected) {
            _this.selectedTab = tab.id;
          }

          _this.$emit("tabAdded", tab);
        } else {
          _this.$emit("tabAddCanceled");
        }
      },

      // Add a header tab to the dock (displayed before the expansion button)
      addHeaderTab(tab, position) {
        let notCanceled = _this.$emitAnkEvent("beforeTabAdd", {
          tab: tab
        });

        if (notCanceled) {
          position === undefined
            ? _this.headerTabs.push(tab)
            : _this.headerTabs.splice(position, 0, tab);
          if (tab.selected) {
            _this.selectedTab = tab.id;
          }

          _this.$emit("tabAdded", tab);
        } else {
          _this.$emit("tabAddCanceled");
        }
      },

      // Add a footer tab to the dock (displayed at the bottom or the right of the dock)
      addFooterTab(tab, position) {
        let notCanceled = _this.$emitAnkEvent("beforeTabAdd", {
          tab: tab
        });

        if (notCanceled) {
          position === undefined
            ? _this.footerTabs.push(tab)
            : _this.footerTabs.splice(position, 0, tab);
          if (tab.selected) {
            _this.selectedTab = tab.id;
          }

          _this.$emit("tabAdded", tab);
        } else {
          _this.$emit("tabAddCanceled");
        }
      },

      // Remove a tab with its Id
      removeTabWithId(id) {
        let currentTabsArea;
        let positionToRemove;

        let headerIndex = _this.headerTabs.findIndex(
          element => element.id === id
        );
        let tabIndex = _this.tabs.findIndex(element => element.id === id);
        let footerIndex = _this.footerTabs.findIndex(
          element => element.id === id
        );

        if (headerIndex !== -1) {
          currentTabsArea = _this.headerTabs;
          positionToRemove = headerIndex;
        } else if (tabIndex !== -1) {
          currentTabsArea = _this.tabs;
          positionToRemove = tabIndex;
        } else if (footerIndex !== -1) {
          currentTabsArea = _this.footerTabs;
          positionToRemove = footerIndex;
        }

        if (positionToRemove !== undefined) {
          let notCanceled = _this.$emitAnkEvent("beforeTabRemove", {
            tab: currentTabsArea[positionToRemove]
          });

          if (notCanceled) {
            let removed = currentTabsArea[positionToRemove];
            currentTabsArea.splice(positionToRemove, 1);
            _this.$emit("tabRemoved", removed);
          } else {
            _this.$emit("tabRemoveCanceled");
          }
        }
      },

      // Used to add the class 'selected' to the current selected tab, to highlight it with CSS
      selectedSelectable(tab) {
        if (tab.selectable) {
          if (tab.id === _this.selectedTab) {
            return "selectable selected";
          } else {
            return "selectable";
          }
        }

        return "";
      }
    };

    this.$dockEventBus.$on("tabLoaded", tab => {
      this.privateScope.addTabToDock(tab);
    });

    this.$dockEventBus.$on("headerTabLoaded", tab => {
      this.privateScope.addHeaderTab(tab);
    });

    this.$dockEventBus.$on("footerTabLoaded", tab => {
      this.privateScope.addFooterTab(tab);
    });

    this.expandedDock = this.expanded;
    if (this.expanded) {
      this.size = this.largeSize;
    } else {
      this.size = this.compactSize;
    }
  },

  mounted() {
    this.$emitAnkEvent("dockLoaded", {
      header: this.headerTabs,
      tabs: this.tabs,
      footer: this.footerTabs
    });
  }
};
