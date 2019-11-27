import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { DropdownsInstaller } from "@progress/kendo-dropdowns-vue-wrapper";
import { GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import { TreeViewInstaller } from "@progress/kendo-treeview-vue-wrapper";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.toolbar";
import "@progress/kendo-ui/js/kendo.treeview";
import Vue from "vue";
import { Component, Watch } from "vue-property-decorator";

Vue.use(ButtonsInstaller);
Vue.use(GridInstaller);
Vue.use(TreeViewInstaller);
Vue.use(DropdownsInstaller);
Vue.use(DataSourceInstaller);
declare var $;
declare var kendo;
@Component({
  components: {
    AnkSmartElement,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterAccountController extends Vue {
  public $refs!: {
    [key: string]: any;
  };
  public groupTree = new kendo.data.HierarchicalDataSource({
    schema: {
      model: {
        children: "items",
        id: "hierarchicalId"
      }
    },
    transport: {
      read: options => {
        this.$http
          .get("/api/v2/admin/account/groups/")
          .then(response => {
            if (response.status === 200 && response.statusText === "OK") {
              let groups: ITreeElement[] = response.data.groups;
              Object.values(groups).forEach(currentData => {
                currentData.items = currentData.items || [];
                currentData.parents.forEach(parentData => {
                  try {
                    groups[parentData].items = groups[parentData].items || [];
                    groups[parentData].items.push(currentData);
                  } catch (e) {
                    // no test here
                  }
                });
              });
              // Suppress first level elements
              Object.values(groups).forEach(currentData => {
                if (currentData.parents.length > 0) {
                  delete groups[currentData.accountId];
                }
              });

              try {
                // Suppress refs elements and keep only values
                groups = Object.values(JSON.parse(JSON.stringify(groups)));
              } catch (e) {
                groups = [];
              }
              const addUniqId = (currentElement, id = "") => {
                currentElement.hierarchicalId = id ? id + "/" + currentElement.documentId : currentElement.documentId;
                if (currentElement.items) {
                  currentElement.items.forEach(childrenElement => {
                    addUniqId(childrenElement, currentElement.hierarchicalId);
                  });
                }
              };
              groups.forEach(currentGroup => {
                addUniqId(currentGroup);
              });
              const selectedElement = window.localStorage.getItem("admin.account.groupSelected");
              const restoreExpandedTree = (data, expanded) => {
                for (const currentData of data) {
                  if (expanded["#all"] === true || expanded[currentData.hierarchicalId]) {
                    currentData.expanded = true;
                  }
                  if (currentData.hierarchicalId === selectedElement) {
                    currentData.selected = true;
                  }
                  if (currentData.items && currentData.items.length) {
                    restoreExpandedTree(currentData.items, expanded);
                  }
                }
              };
              let expandedElements = window.localStorage.getItem("admin.account.expandedElement");
              if (expandedElements) {
                try {
                  expandedElements = JSON.parse(expandedElements);
                  restoreExpandedTree(groups, expandedElements);
                } catch (e) {
                  // no test here
                }
              }
              const toDisplay = [
                {
                  accountId: "@users",
                  documentId: "@users",
                  expanded: expandedElements && (expandedElements["#all"] || expandedElements["@users"]),
                  hierarchicalId: "@users",
                  items: groups,
                  login: "@users",
                  nbUser: response.data.nbUsers ? response.data.nbUsers : "??",
                  parents: [],
                  selected: "@users" === selectedElement,
                  title: "All users"
                }
              ];
              options.success(toDisplay);
            } else {
              throw new Error("Unable to get groups");
            }
          })
          .catch(error => {
            console.error("Unable to get group", error);
          });
      }
    }
  });
  public gridContent = new kendo.data.DataSource({
    pageSize: 20,
    schema: {
      data: "data",
      model: {
        fields: {
          firstname: { type: "string" },
          lastname: { type: "string" },
          login: { type: "string" },
          mail: { type: "string" }
        },
        id: "id"
      },
      total: "total"
    },
    serverFiltering: true,
    serverPaging: true,
    serverSorting: true,
    transport: {
      read: {
        url: "/api/v2/admin/account/users/"
      }
    }
  });
  public userModeSelected: boolean = false;
  public displayGroupDocument: boolean = false;
  public selectedGroupDocumentId: boolean = false;
  public selectedGroupLogin: boolean = false;
  public selectedUser: string = "";
  public options: object = {};
  public groupId: any = false;
  public groupTitle: any = false;
  private smartTriggerActivated: boolean = false;
  private refreshNeeded: boolean = false;
  @Watch("refreshNeeded")
  public watchRefreshNeeded(value) {
    if (value) {
      setTimeout(() => {
        this.updateTreeData();
      }, 300);
    }
    this.refreshNeeded = false;
  }

  @Watch("groupId")
  public watchGroupId(value) {
    if (this.$refs.groupList) {
      const createGrpBtn = this.$refs.groupList.kendoWidget();
      if (value === "@users") {
        createGrpBtn.setOptions({ optionLabel: "Create group" });
      } else {
        createGrpBtn.setOptions({ optionLabel: "Create sub group" });
      }
    }
  }

  public mounted() {
    this.$nextTick(() => {
      this.groupId = window.localStorage.getItem("admin.account.groupSelected.id");
      this.fetchConfig();
      this.bindTree();
    });
  }

  // Get the config of the creation toolbar
  public fetchConfig() {
    this.$http
      .get("api/v2/admin/account/config/")
      .then(response => {
        if (response.status === 200 && response.statusText === "OK") {
          this.options = response.data;
          // this.bindToolbars(response.data);
        } else {
          throw new Error(response.data);
        }
      })
      .catch(error => {
        console.error("Unable to get options", error);
      });
  }

  // Bind the tree events
  public bindTree() {
    const treeview = this.$refs.groupTreeView.kendoWidget();
    treeview.bind("dataBound", () => {
      const selectedElement = treeview.dataItem(treeview.select());
      if (selectedElement) {
        if (selectedElement.login && this.selectedGroupLogin !== selectedElement.documentId) {
          this.updateGridData(selectedElement.login);
        }
        if (selectedElement.documentId) {
          this.updateGroupSelected(selectedElement.documentId);
        }
      }
    });
  }

  public parseCreateUser(data) {
    return data.user;
  }

  public parseCreateGroup(data) {
    return data.group;
  }

  // Bind the grid events (click to open an user)
  public openUser(event) {
    event.preventDefault();
    const grid = this.$refs.grid.kendoWidget();
    const $tr = $(event.currentTarget).closest("tr");
    const dataItem = grid.dataItem($tr);
    const userId = dataItem.id;

    this.selectedUser = userId;
    this.$nextTick(() => {
      if (!this.$refs.grid.kendoWidget()._data) {
        this.gridContent.read();
      }
      if (userId) {
        const openDoc = this.$refs.openDoc;
        if (openDoc) {
          openDoc.fetchSmartElement({
            initid: userId,
            viewId: "!defaultConsultation"
          });
          this.refreshData(openDoc);
        }
      }
    });
  }

  // Manually refresh the tree pane
  public updateTreeData() {
    let filterTitle;
    if (this.$refs.filterTree.value) {
      filterTitle = this.$refs.filterTree.value.toLowerCase();
    }
    if (filterTitle !== undefined) {
      this.filter(this.groupTree, filterTitle);
    } else {
      this.showAll(this.groupTree);
      this.expandAll();
    }
  }

  // filter treeview datasource and expand until leaf is reached if a matching item is found
  public filter(dataSource, query) {
    let hasVisibleChildren = false;
    const data = dataSource instanceof kendo.data.HierarchicalDataSource && dataSource.data();

    // tslint:disable-next-line:prefer-for-of
    for (let i = 0; i < data.length; i++) {
      const item = data[i];
      const text = item.title.toLowerCase();
      const itemVisible =
        query === true || // parent already matches
        query === "" || // query is empty
        text.indexOf(query) >= 0; // item title matches query

      const anyVisibleChildren = this.filter(item.children, query);

      hasVisibleChildren = hasVisibleChildren || anyVisibleChildren || itemVisible;

      item.hidden = !itemVisible && !anyVisibleChildren;
    }

    if (data) {
      // re-apply filter on children
      dataSource.filter({ field: "hidden", operator: "neq", value: true });
      const treeview = this.$refs.groupTreeView.kendoWidget();
      treeview.expand(".k-item");
    }

    return hasVisibleChildren;
  }

  public showAll(dataSource) {
    const data = dataSource instanceof kendo.data.HierarchicalDataSource && dataSource.data();
    // tslint:disable-next-line:prefer-for-of
    for (let i = 0; i < data.length; i++) {
      const item = data[i];
      item.hidden = false;
      if (item.hasChildren) {
        this.showAll(item.children);
      }
    }
    return this.groupTree.filter({
      field: "hidden",
      operators: "eq",
      value: false
    });
  }
  // Display the selected group in the ank-document
  public updateGroupSelected(selectedGroupId) {
    this.selectedGroupLogin = selectedGroupId || this.selectedGroupLogin;
    if (selectedGroupId && selectedGroupId !== "@users") {
      this.selectedGroupDocumentId = selectedGroupId;
      this.displayGroupDocument = true;
      return;
    }
    this.displayGroupDocument = false;
  }

  // Refresh the with the new selected group
  public updateGridData(selectedGroupLogin?) {
    if (selectedGroupLogin === "@users") {
      this.gridContent.filter({});
    } else {
      this.gridContent.filter({
        field: "group",
        operator: "equal",
        value: selectedGroupLogin
      });
    }
  }

  public openGroup() {
    this.selectedUser = this.groupId;
    this.$nextTick(() => {
      const openDoc = this.$refs.openDoc;
      if (openDoc) {
        openDoc.fetchSmartElement({
          initid: this.groupId,
          viewId: "!defaultConsultation"
        });
        this.refreshData(openDoc);
      }
    });
  }
  public selectCreateUserConfig(e) {
    if (e.dataItem.canCreate) {
      this.selectedUser = e.dataItem.id;
      this.$nextTick(() => {
        e.sender.value(""); // reset dropdownn
        const openDoc = this.$refs.openDoc;
        if (openDoc) {
          this.refreshData(openDoc);
          openDoc.fetchSmartElement({
            customClientData: { defaultGroup: this.selectedGroupDocumentId },
            initid: this.selectedUser,
            viewId: "!defaultCreation"
          });
        }
      });
    }
  }
  public addClassOnSelectorContainer(e) {
    e.sender.popup.element.addClass("select-container");
  }
  public selectCreateGroupConfig(e) {
    if (e.dataItem.canCreate) {
      this.selectedUser = e.dataItem.id;
      this.$nextTick(() => {
        e.sender.value(""); // reset dropdownn
        const openDoc = this.$refs.openDoc;
        if (openDoc) {
          this.refreshData(openDoc);
          openDoc.fetchSmartElement({
            customClientData: { defaultGroup: this.selectedGroupDocumentId },
            initid: this.selectedUser,
            viewId: "!defaultCreation"
          });
        }
      });
    }
  }

  // Open group selected in group change mode
  public openChangeGroup() {
    this.selectedUser = this.groupId;
    this.$nextTick(() => {
      const openDoc = this.$refs.openDoc;
      if (openDoc) {
        openDoc.fetchSmartElement({
          initid: this.groupId,
          viewId: "changeGroup"
        });
      }
    });
  }

  // Update the selected group
  public onGroupSelect(event) {
    const selectedElement = event.sender.dataItem(event.sender.select());
    window.localStorage.setItem("admin.account.groupSelected", selectedElement.hierarchicalId);
    window.localStorage.setItem("admin.account.groupSelected.id", selectedElement.documentId);
    this.updateGridData(selectedElement.login);
    this.updateGroupSelected(selectedElement.documentId);
    this.groupTitle = selectedElement.title;
    this.groupId = selectedElement.documentId;
  }

  // Register the leaf open and closed
  public registerTreeState() {
    const saveTreeView = () => {
      const treeview = this.$refs.groupTreeView.kendoWidget();
      const expandedItemsIds = {};
      treeview.element.find(".k-item").each(function(this: any) {
        const item = treeview.dataItem(this);
        if (item.expanded) {
          expandedItemsIds[item.hierarchicalId] = true;
        }
      });
      window.localStorage.setItem("admin.account.expandedElement", JSON.stringify(expandedItemsIds));
    };
    window.setTimeout(saveTreeView, 100);
  }
  public refreshData(openDoc) {
    if (!this.smartTriggerActivated) {
      openDoc.addEventListener("afterSave", () => {
        this.gridContent.read();
        this.groupTree.read();
        this.refreshNeeded = true;
      });
      openDoc.addEventListener("afterDelete", () => {
        this.updateGridData();
        this.groupTree.read();
        this.refreshNeeded = true;
      });
      this.smartTriggerActivated = true;
      this.refreshNeeded = false;
    }
  }

  // Close all the leafs
  public collapseAll() {
    window.localStorage.setItem("admin.account.expandedElement", JSON.stringify({ "#all": false }));
    const treeview = this.$refs.groupTreeView.kendoWidget();
    treeview.collapse(".k-item");
  }

  // Expand all the leafs
  public expandAll() {
    window.localStorage.setItem("admin.account.expandedElement", JSON.stringify({ "#all": true }));
    const treeview = this.$refs.groupTreeView.kendoWidget();
    treeview.expand(".k-item");
  }

  // Disable all the group non selected
  public filterGroup(event) {
    event.preventDefault();
    this.updateTreeData();
  }
}
