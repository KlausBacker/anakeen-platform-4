import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
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
    "ank-splitter": AnkSplitter
  }
})
export default class AdminCenterAccountController extends Vue {
  public mainPanes: object[] = [
    {
      collapsible: true,
      resizable: true,
      scrollable: false,
      size: "50%"
    },
    {
      collapsible: true,
      resizable: true,
      scrollable: false
    }
  ];

  public panes: object[] = [
    {
      collapsible: true,
      min: "300px",
      resizable: true,
      scrollable: false,
      size: "20%"
    },
    {
      collapsible: true,
      resizable: true,
      scrollable: false
    }
  ];
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
  public groupId: any = false;
  public groupTitle: any = false;
  private smartTriggerActivated: boolean = false;

  @Watch("groupId")
  public watchGroupId(value) {
    const createGrpBtn = this.$refs.groupList.kendoWidget();
    if (value === "@users") {
      createGrpBtn.setOptions({ optionLabel: "Create group" });
    } else {
      createGrpBtn.setOptions({ optionLabel: "Create sub group" });
    }
  }

  public mounted() {
    this.$refs.accountTreeSplitter.disableEmptyContent();
    this.$nextTick(() => {
      this.groupId = window.localStorage.getItem(
        "admin.account.groupSelected.id"
      );
      this.fetchConfig();
      this.bindTree();
      this.bindSplitter();
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
        console.log(this.selectedGroupLogin, selectedElement);

        if (
          selectedElement.login &&
          this.selectedGroupLogin !== selectedElement.documentId
        ) {
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
    this.$refs.accountSplitter.disableEmptyContent();
    const grid = this.$refs.grid.kendoWidget();
    const $tr = $(event.currentTarget).closest("tr");
    const dataItem = grid.dataItem($tr);
    const userId = dataItem.id;
    this.$nextTick(() => {
      if (!this.$refs.grid.kendoWidget()._data) {
        this.gridContent.read();
      }
      if (userId) {
        const openDoc = this.$refs.openDoc;
        if (openDoc) {
          if (openDoc.isLoaded()) {
            openDoc.fetchSmartElement({
              initid: userId,
              viewId: "!defaultConsultation"
            });
            this.refreshData(openDoc);
          } else {
            openDoc.$once("documentLoaded", () => {
              openDoc.fetchSmartElement({
                initid: userId,
                viewId: "!defaultConsultation"
              });
              this.refreshData(openDoc);
            });
          }
        }
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
    this.$refs.accountSplitter.disableEmptyContent();
    this.$nextTick(() => {
      const openDoc = this.$refs.openDoc;
      if (openDoc) {
        if (openDoc.isLoaded()) {
          openDoc.fetchSmartElement({
            initid: this.groupId,
            viewId: "!defaultConsultation"
          });
          this.refreshData(openDoc);
        } else {
          openDoc.$once("documentLoaded", () => {
            openDoc.fetchSmartElement({
              initid: this.groupId,
              viewId: "!defaultConsultation"
            });
            this.refreshData(openDoc);
          });
        }
      }
    });
  }
  public selectCreateUserConfig(e) {
    if (e.dataItem.canCreate) {
      this.$refs.accountSplitter.disableEmptyContent();
      this.$nextTick(() => {
        const openDoc = this.$refs.openDoc;
        if (openDoc) {
          if (openDoc.isLoaded()) {
            this.refreshData(openDoc);
            openDoc.fetchSmartElement({
              customClientData: { defaultGroup: this.selectedGroupDocumentId },
              initid: e.dataItem.id,
              viewId: "!defaultCreation"
            });
          } else {
            openDoc.$once("documentLoaded", () => {
              this.refreshData(openDoc);
              openDoc.fetchSmartElement({
                customClientData: {
                  defaultGroup: this.selectedGroupDocumentId
                },
                initid: e.dataItem.id,
                viewId: "!defaultCreation"
              });
            });
          }
        }
      });
    }
  }
  public addClassOnSelectorContainer(e) {
    e.sender.popup.element.addClass("select-container");
  }
  public selectCreateGroupConfig(e) {
    if (e.dataItem.canCreate) {
      this.$refs.accountSplitter.disableEmptyContent();
      this.$nextTick(() => {
        const openDoc = this.$refs.openDoc;
        if (openDoc) {
          if (openDoc.isLoaded()) {
            this.refreshData(openDoc);
            openDoc.fetchSmartElement({
              customClientData: { defaultGroup: this.selectedGroupDocumentId },
              initid: e.dataItem.id,
              viewId: "!defaultCreation"
            });
          } else {
            openDoc.$once("documentLoaded", () => {
              this.refreshData(openDoc);
              openDoc.fetchSmartElement({
                customClientData: {
                  defaultGroup: this.selectedGroupDocumentId
                },
                initid: e.dataItem.id,
                viewId: "!defaultCreation"
              });
            });
          }
        }
      });
    }
  }

  // Open group selected in group change mode
  public openChangeGroup() {
    this.$refs.accountSplitter.disableEmptyContent();
    this.$nextTick(() => {
      const openDoc = this.$refs.openDoc;
      if (openDoc) {
        if (openDoc.isLoaded()) {
          openDoc.fetchSmartElement({
            initid: this.groupId,
            viewId: "changeGroup"
          });
        } else {
          openDoc.$once("documentLoaded", () => {
            openDoc.fetchSmartElement({
              initid: this.groupId,
              viewId: "changeGroup"
            });
            this.refreshData(openDoc);
          });
        }
      }
    });
  }

  // Update the selected group
  public onGroupSelect(event) {
    const selectedElement = event.sender.dataItem(event.sender.select());
    window.localStorage.setItem(
      "admin.account.groupSelected",
      selectedElement.hierarchicalId
    );
    window.localStorage.setItem(
      "admin.account.groupSelected.id",
      selectedElement.documentId
    );
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
      window.localStorage.setItem(
        "admin.account.expandedElement",
        JSON.stringify(expandedItemsIds)
      );
    };
    window.setTimeout(saveTreeView, 100);
  }
  public refreshData(openDoc) {
    if (!this.smartTriggerActivated) {
      openDoc.addEventListener("afterSave", () => {
        this.gridContent.read();
        this.updateTreeData(true);
      });
      openDoc.addEventListener("afterDelete", () => {
        this.updateGridData();
        this.updateTreeData(true);
      });
      this.smartTriggerActivated = true;
    }
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
