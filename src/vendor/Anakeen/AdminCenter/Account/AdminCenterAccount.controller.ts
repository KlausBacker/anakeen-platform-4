import { AnkSmartElement } from "@anakeen/user-interfaces";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import { GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import { TreeViewInstaller } from "@progress/kendo-treeview-vue-wrapper";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.toolbar";
import "@progress/kendo-ui/js/kendo.treeview";
import Vue from "vue";
import Component from "vue-class-component";

Vue.use(ButtonsInstaller);
Vue.use(GridInstaller);
Vue.use(TreeViewInstaller);
declare var $;
declare var kendo;

alert("toto");

@Component({
  components: {
    AnkSmartElement
  }
})
export default class AdminCenterAccountController extends Vue {
  public $refs!: {
    [key: string]: any;
    openDoc: AnkSmartElement;
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
                currentElement.hierarchicalId = id
                  ? id + "/" + currentElement.documentId
                  : currentElement.documentId;
                if (currentElement.items) {
                  currentElement.items.forEach(childrenElement => {
                    addUniqId(childrenElement, currentElement.hierarchicalId);
                  });
                }
              };
              groups.forEach(currentGroup => {
                addUniqId(currentGroup);
              });
              const selectedElement = window.localStorage.getItem(
                "admin.account.groupSelected"
              );
              const restoreExpandedTree = (data, expanded) => {
                for (const currentData of data) {
                  if (
                    expanded["#all"] === true ||
                    expanded[currentData.hierarchicalId]
                  ) {
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
              let expandedElements = window.localStorage.getItem(
                "admin.account.expandedElement"
              );
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
                  expanded:
                    expandedElements &&
                    (expandedElements["#all"] || expandedElements["@users"]),
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
    pageSize: 10,
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
  public options: object = {};

  public mounted() {
    this.fetchConfig();
    this.bindTree();
    this.bindGrid();
    this.bindSplitter();
    this.bindEditDoc();
  }

  // Get the config of the creation toolbar
  public fetchConfig() {
    this.$http
      .get("api/v2/admin/account/config/")
      .then(response => {
        if (response.status === 200 && response.statusText === "OK") {
          this.options = response.data;
          this.bindToolbars(response.data);
        } else {
          throw new Error(response.data);
        }
      })
      .catch(error => {
        console.error("Unable to get options", error);
      });
  }

  public bindToolbars(element) {
    const openDoc = this.$refs.openDoc;
    const groupToolbar = this.$refs.groupToolbar.kendoWidget();
    const toggleUserMode = this.toggleUserMode.bind(this);
    const openInCreation = event => {
      if (event && event.target && event.target[0] && event.target[0].id) {
        if (
          event.target[0].id === "groupCreateToolbar" ||
          event.target[0].id === "userCreateToolbar"
        ) {
          event.preventDefault();
          $(event.target[0])
            .parent()
            .data("kendoPopup")
            .open();
          return;
        }
        openDoc.fetchSmartElement({
          customClientData: { defaultGroup: this.selectedGroupDocumentId },
          initid: event.target[0].id,
          viewId: "!defaultCreation"
        });
        toggleUserMode();
      }
    };
    groupToolbar.add({
      id: "groupCreateToolbar",
      menuButtons: element.group,
      text: "Create",
      type: "splitButton"
    });
    groupToolbar.bind("click", openInCreation);
    const userToolbar = this.$refs.userToolbar.kendoWidget();
    userToolbar.add({
      id: "userCreateToolbar",
      menuButtons: element.user,
      text: "Create",
      type: "splitButton"
    });
    userToolbar.bind("click", openInCreation);
  }

  // Bind the tree events
  public bindTree() {
    const treeview = this.$refs.groupTreeView.kendoWidget();
    treeview.bind("dataBound", () => {
      const selectedElement = treeview.dataItem(treeview.select());
      if (selectedElement) {
        if (selectedElement.documentId) {
          this.updateGroupSelected(selectedElement.documentId);
        }
        if (selectedElement.login) {
          this.updateGridData(selectedElement.login);
        }
      }
    });
  }

  // Bind the grid events (click to open an user)
  public bindGrid() {
    const grid = this.$refs.grid.$el;
    const openDoc = this.$refs.openDoc;
    const toggleUserMode = this.toggleUserMode.bind(this);
    $(grid).on("click", ".openButton", event => {
      event.preventDefault();
      const userId = event.currentTarget.dataset.initid;
      if (userId) {
        // Set props because publicMethods fetchSmartElement is not accessible
        // until document is loaded
        openDoc.seValue = JSON.stringify({
          initid: userId,
          viewId: "!defaultConsultation"
        });
        toggleUserMode();
      }
    });
  }

  // Create the splitter system
  public bindSplitter() {
    const onContentResize = (part, $split) => {
      return () => {
        window.setTimeout(() => {
          $(window).trigger("resize");
        }, 100);
        window.localStorage.setItem(
          "admin.account." + part,
          $($split)
            .data("kendoSplitter")

            .size(".k-pane:first")
        );
      };
    };
    const sizeContentPart =
      window.localStorage.getItem("admin.account.content") || "200px";
    const sizeCenterPart =
      window.localStorage.getItem("admin.account.center") || "200px";
    $(this.$refs.gridAndTreePart).kendoSplitter({
      panes: [
        {
          collapsible: true,
          min: "200px",
          resizable: true,
          size: sizeContentPart
        },
        { collapsible: false, resizable: true }
      ],
      resize: onContentResize("content", this.$refs.gridAndTreePart)
    });
    $(this.$refs.centerPart).kendoSplitter({
      orientation: "vertical",
      panes: [
        {
          collapsible: true,
          min: "200px",
          resizable: true,
          size: sizeCenterPart
        },
        { collapsible: false, resizable: true }
      ],
      resize: onContentResize("center", this.$refs.centerPart)
    });
  }

  public bindEditDoc() {
    const openDoc = this.$refs.openDoc;
    openDoc.$el.addEventListener("afterSave", event => {
      if (
        event &&
        event.detail &&
        event.detail[1] &&
        event.detail[1] &&
        event.detail[1].type &&
        event.detail[1].type === "folder"
      ) {
        this.updateTreeData(true);
      } else {
        this.updateGridData();
      }
    });
  }

  // Display the user pane
  public toggleUserMode() {
    this.userModeSelected = !this.userModeSelected;
  }

  // Manually refresh the tree pane
  public updateTreeData(force?) {
    const filterTitle = this.$refs.filterTree.value
      ? this.$refs.filterTree.value.toLowerCase()
      : "";
    if (force) {
      this.groupTree.read();
    }
    if (filterTitle) {
      return this.groupTree.filter({
        field: "title",
        operator: "contains",
        value: filterTitle
      });
    }
    this.groupTree.filter({});
  }

  // Display the selected group in the ank-document
  public updateGroupSelected(selectedGroupId) {
    const groupDoc = this.$refs.groupDoc;
    this.selectedGroupLogin = selectedGroupId || this.selectedGroupLogin;
    if (selectedGroupId && selectedGroupId !== "@users") {
      this.selectedGroupDocumentId = selectedGroupId;
      this.displayGroupDocument = true;
      if (groupDoc.isLoaded()) {
        groupDoc.fetchSmartElement({
          initid: selectedGroupId,
          viewId: "!defaultConsultation"
        });
      } else {
        groupDoc.$once("documentLoaded", () => {
          groupDoc.fetchSmartElement({
            initid: selectedGroupId,
            viewId: "!defaultConsultation"
          });
        });
      }
      return;
    }
    this.displayGroupDocument = false;
  }

  // Refresh the with the new selected group
  public updateGridData(selectedGroupLogin?) {
    const grid = this.$refs.grid.kendoWidget();
    grid.clearSelection();
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

  // Open group selected in group change mode
  public openChangeGroup() {
    const openDoc = this.$refs.openDoc;
    openDoc.fetchSmartElement({
      initid: this.selectedGroupDocumentId,
      viewId: "changeGroup"
    });

    this.toggleUserMode();
  }

  // Update the selected group
  public onGroupSelect(event) {
    const selectedElement = event.sender.dataItem(event.sender.select());
    window.localStorage.setItem(
      "admin.account.groupSelected",
      selectedElement.hierarchicalId
    );
    this.updateGroupSelected(selectedElement.documentId);
    this.updateGridData(selectedElement.login);
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
      window.localStorage.setItem(
        "admin.account.expandedElement",
        JSON.stringify(expandedItemsIds)
      );
    };
    window.setTimeout(saveTreeView, 100);
  }

  // Close all the leafs
  public collapseAll() {
    window.localStorage.setItem(
      "admin.account.expandedElement",
      JSON.stringify({ "#all": false })
    );
    this.updateTreeData();
  }

  // Expand all the leafs
  public expandAll() {
    window.localStorage.setItem(
      "admin.account.expandedElement",
      JSON.stringify({ "#all": true })
    );
    this.updateTreeData();
  }

  // Disable all the group non selected
  public filterGroup(event) {
    event.preventDefault();
    this.updateTreeData(true);
  }
}
