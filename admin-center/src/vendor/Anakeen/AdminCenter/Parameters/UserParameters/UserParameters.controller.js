import parameterEditor from "../ParameterEditor/ParameterEditor.vue";

export default {
  name: "admin-center-user-parameters",

  components: {
    "admin-center-parameters-editor": parameterEditor
  },

  data() {
    return {
      // Data source for user parameters treeList
      userParametersDataSource: [],

      // Current edited item and route url to modify it
      editedItem: null,
      editRoute: "",

      // Login of the selected user
      actualLogin: "",

      // Value entered in the search input
      inputSearchValue: "",

      // Memorize kendo components
      deleteConfirmationWindow: null,
      deleteErrorWindow: null,
      usersGrid: null,
      parametersTreeList: null
    };
  },

  methods: {
    // Init the grid containing users
    initUsersGrid() {
      this.usersGrid = this.$(".users-grid", this.$el)
        .kendoGrid({
          columns: [
            { field: "login", title: "Login" },
            { field: "firstname", title: "First name" },
            { field: "lastname", title: "Last name" },
            {
              width: "14rem",
              filterable: false,

              // Button to select the user and access his parameters
              template: '<a class="selection-btn">Select user</a>'
            }
          ],

          // Datasource set to display the users in a grid
          dataSource: {
            transport: {
              read: options => {
                this.$ankApi
                  .get("admin/parameters/users/")
                  .then(options.success)
                  .catch(options.error);
              }
            },
            schema: {
              data: response => response.data.data.users,
              total: response => response.data.data.total
            },
            serverPaging: true,
            pageSize: 10
          },

          pageable: {
            pageSize: 10
          },

          // Disable columns filters to add global filter
          filterable: false,
          resizable: false,
          selectable: "rows",
          messages: {
            noRows: "Search a user to modify his settings"
          },
          dataBound: () => {
            // Init kendoButtons when users are loaded from the server
            this.$(".selection-btn", this.$el).kendoButton({
              icon: "user"
            });
            this.restoreTreeState();
          }
        })
        .on("click", ".selection-btn", e => {
          // Select a user to display his parameters with the data item
          let grid = this.usersGrid;
          grid.select(e.currentTarget.parentNode.parentNode);
          let dataItem = grid.dataItem(grid.select());
          this.selectUser(dataItem);
          grid.clearSelection();
        })
        .data("kendoGrid");
    },

    // Init the treeList containing all the parameters for the selected user
    initTreeList() {
      // Custom toolbar template to add a global filter
      let toolbarTemplate = `
        <div class="user-parameters-toolbar">
            <a class="back-btn">Search another user</a>
            <a class="refresh-btn"></a>
            <a class="expand-btn"></a>
            <a class="collapse-btn"></a>
            <div class="input-group">
                <input type="text"
                       class="form-control global-search-input"
                       placeholder="Filter parameters...">
                <i class="input-group-addon material-icons reset-search-btn parameter-search-reset">close</i>
                <div class="input-group-append">
                    <button class="btn btn-secondary filter-btn">Filter</button>
                </div>
            </div>
        </div>
      `;

      // Add a class on filterable columns header to diplay a filter icon when filtering
      let headerAttributes = { class: "user-filterable-header" }; // jscs:ignore disallowQuotedKeysInObjects

      this.parametersTree = this.$(".user-parameters-tree", this.$el)
        .kendoTreeList({
          dataSource: this.userParametersDataSource,
          columns: [
            {
              field: "name",
              headerTemplate: '<a class="column-title">Name</a>',
              headerAttributes: headerAttributes
            },
            {
              field: "description",
              headerTemplate: '<a class="column-title">Description</a>',
              headerAttributes: headerAttributes
            },
            {
              field: "value",
              headerTemplate: '<a class="column-title">User value</a>',
              headerAttributes: headerAttributes
            },
            {
              field: "initialValue",
              headerTemplate: '<a class="column-title">System value</a>',
              headerAttributes: headerAttributes
            },
            {
              width: "10rem",
              filterable: false,

              // Display edition button on modifiable parameters
              // and restore/delete button on user defined parameters
              template:
                "# if (!data.rowLevel && !data.isStatic && !data.isReadOnly) { #" +
                '<a class="edition-btn" title="Edit"></a>' +
                "# if (data.forUser) { #" +
                '<a class="delete-btn" title="Restore system value"></a>' +
                "# } #" +
                "# } #"
            }
          ],

          // Disable filter on columns to add a global filter
          filterable: false,
          toolbar: toolbarTemplate,
          resizable: false,
          maxWidth: "100%",
          maxHeight: "100%",

          expand: e => {
            this.addClassToRow(e.sender);
            this.saveTreeState();
          },

          collapse: e => {
            this.addClassToRow(e.sender);
            this.saveTreeState();
          },

          dataBound: e => {
            this.addClassToRow(e.sender);
            this.restoreTreeState();

            // Init kendoButtons in tree
            this.$(".edition-btn", this.$el).kendoButton({
              icon: "edit"
            });
            this.$(".delete-btn", this.$el).kendoButton({
              icon: "undo"
            });
          }
        })
        .on("click", ".edition-btn", e => {
          // Open parameter editor with selected dataItem
          let treeList = this.parametersTree;
          let dataItem = treeList.dataItem(e.currentTarget);
          this.openEditor(dataItem);
        })
        .on("click", ".delete-btn", e => {
          // Delete/Restore user parameter with selected dataItem
          let treeList = this.parametersTree;
          let dataItem = treeList.dataItem(e.currentTarget);
          this.deleteParameter(dataItem);
        })
        .on("click", ".back-btn", () => {
          // Reset actual login when returning to user selection
          this.actualLogin = "";

          // Display user search
          this.$(".user-search", this.$el).css("display", "");
          this.$(".parameters-div", this.$el).attr(
            "style",
            (i, s) => s + "display: none !important;"
          );

          // Focus on search input
          this.$(".user-search-input", this.$el).focus();

          // Resize users grid when it is displayed
          this.resizeUsersGrid();
        })
        .on("click", ".refresh-btn", () => {
          // Re-fetch data from server
          kendo.ui.progress(this.$(".user-parameters-tree", this.$el), true);
          this.userParametersDataSource
            .read()
            .then(() => {
              kendo.ui.progress(
                this.$(".user-parameters-tree", this.$el),
                false
              );
              document.querySelector(".ank-notifier").dispatchEvent(
                new CustomEvent("ankNotification", {
                  detail: [
                    {
                      content: {
                        title: "Parameters loaded",
                        textContent:
                          "Parameters successfully loaded from server"
                      },
                      type: "success"
                    }
                  ]
                })
              );
            })
            .catch(() => {
              kendo.ui.progress(
                this.$(".user-parameters-tree", this.$el),
                false
              );
              document.querySelector(".ank-notifier").dispatchEvent(
                new CustomEvent("ankNotification", {
                  detail: [
                    {
                      content: {
                        title: "Parameters loading failed",
                        textContent: "Loading of parameters from server failed"
                      },
                      type: "error"
                    }
                  ]
                })
              );
            });
        })
        .on("click", ".expand-btn", () => this.expand(true))
        .on("click", ".collapse-btn", () => this.expand(false))
        .on("click", ".filter-btn", () =>
          this.searchParameters(this.$(".global-search-input", this.$el).val())
        )
        .on("keyup", ".global-search-input", e => {
          if (e.key === "Enter") {
            this.searchParameters(
              this.$(".global-search-input", this.$el).val()
            );
          }
        })
        .on("click", ".reset-search-btn", () => {
          this.$(".global-search-input", this.$el).val("");
          this.searchParameters("");
        })
        .data("kendoTreeList");

      // Init kendoButtons of toolbar
      this.$(".back-btn", this.$el).kendoButton({
        icon: "arrow-chevron-left"
      });
      this.$(".refresh-btn", this.$el).kendoButton({
        icon: "reload"
      });
      this.$(".expand-btn", this.$el).kendoButton({
        icon: "arrow-60-down"
      });
      this.$(".collapse-btn", this.$el).kendoButton({
        icon: "arrow-60-up"
      });
    },

    // Select a user in users grid and display his parameters
    selectUser(dataItem) {
      // Set new DataSource
      this.actualLogin = dataItem.login;
      this.userParametersDataSource = new kendo.data.TreeListDataSource({
        transport: {
          read: options => {
            this.$ankApi
              .get("admin/parameters/users/" + this.actualLogin + "/")
              .then(options.success)
              .catch(options.error);
          }
        },
        schema: {
          data: response => response.data.data
        }
      });
      this.parametersTree.setDataSource(this.userParametersDataSource);

      // Display parameters and hide user search
      this.$(".user-search", this.$el).css("display", "none");
      this.$(".parameters-div", this.$el).css("display", "");

      // Resize user parameters treeList when displaying it
      this.resizeUserParametersTree();

      // Focus on filter input
      this.$(".global-search-input", this.$el).focus();
    },

    // Open the parameter editor with the correct dataItem and modification route url
    openEditor(dataItem) {
      this.editedItem = dataItem;
      this.editRoute =
        "admin/parameters/" +
        this.actualLogin +
        "/" +
        dataItem.nameSpace +
        "/" +
        dataItem.name +
        "/";
    },

    // Send a request to the server to remove the user definition of the passed parameter
    // to restore the system value of this parameter for the user
    deleteParameter(dataItem) {
      this.$ankApi
        .delete(
          "admin/parameters/" +
            this.actualLogin +
            "/" +
            dataItem.nameSpace +
            "/" +
            dataItem.name +
            "/"
        )
        .then(() => {
          // Show a confirmation window to notify the user of the modification
          this.deleteConfirmationWindow = this.$(".delete-confirmation-window")
            .kendoWindow({
              modal: true,
              draggable: false,
              resizable: false,
              title: "Parameter restored",
              width: "30%",
              visible: false,
              actions: []
            })
            .data("kendoWindow");

          this.deleteConfirmationWindow.center().open();

          // Init the confirmation window's close kendoButton
          this.$(".delete-confirmation-btn").kendoButton({
            icon: "arrow-chevron-left"
          });

          // Re-fetch the parameters from server to display the updated values
          this.userParametersDataSource.read();
        })
        .catch(() => {
          // Show an error window to notify the user that the parameter restoration failed
          this.deleteErrorWindow = this.$(".delete-error-window")
            .kendoWindow({
              modal: true,
              draggable: false,
              resizable: false,
              title: "Error",
              width: "30%",
              visible: false,
              actions: []
            })
            .data("kendoWindow");

          this.deleteErrorWindow.center().open();

          // Init error window's close kendoButton
          this.$(".delete-error-btn").kendoButton({
            icon: "arrow-chevron-left"
          });
        });
    },

    // Close restoration confirmation window
    closeDeleteConfirmation() {
      this.deleteConfirmationWindow.close();
    },

    // Close restoration error window
    closeDeleteError() {
      this.deleteErrorWindow.close();
    },

    // Search a user on the server in users treeList
    searchUser() {
      let user = this.$(".user-search-input", this.$el).val();
      if (user.trim()) {
        let usersDataSource = new kendo.data.DataSource({
          transport: {
            read: options => {
              this.$ankApi
                .get("admin/parameters/users/search/" + user + "/")
                .then(options.success)
                .catch(options.error);
            }
          },
          schema: {
            data: response => response.data.data.users,
            total: response => response.data.data.total
          },
          serverPaging: true,
          pageSize: 10
        });
        this.usersGrid.setDataSource(usersDataSource);
      }
    },

    // Filter treeList parameters on name, description, value and initial system value
    searchParameters(researchTerms) {
      if (researchTerms) {
        this.userParametersDataSource.filter({
          logic: "or",
          filters: [
            { field: "name", operator: "contains", value: researchTerms },
            {
              field: "description",
              operator: "contains",
              value: researchTerms
            },
            { field: "value", operator: "contains", value: researchTerms },
            {
              field: "initialValue",
              operator: "contains",
              value: researchTerms
            }
          ]
        });

        // Add icon to show filter effect to the user
        if (
          !this.$(".user-filterable-header", this.$el).children(".filter-icon")
            .length
        ) {
          this.$(".user-filterable-header", this.$el).prepend(
            this.$('<i class="material-icons filter-icon">filter_list</i>')
          );
        }

        // Expand treeList to display all results
        this.expand(true);
      } else {
        // Reset filter passing an empty one
        this.userParametersDataSource.filter({});

        // Remove filter icon when nothing is filtered
        this.$(".user-filterable-header", this.$el)
          .children(".filter-icon")
          .remove();
      }
    },

    // Add a class to level 1 and 2 rows of treeList, to add custom CSS
    addClassToRow(treeList) {
      let items = treeList.items();
      let _this = this;

      // setTimeout(function, 0) to add CSS classes when all DOM content has been updated
      setTimeout(() => {
        items.each(function addTypeClass() {
          let dataItem = treeList.dataItem(this);
          if (dataItem.rowLevel) {
            _this
              .$(this)
              .addClass("grid-expandable grid-level-" + dataItem.rowLevel);
          }
        });
      }, 0);
    },

    // Expand/Collapse every rows of the user parameters tree list (true => expand / false => collapse)
    expand(expansion) {
      let treeList = this.$(".user-parameters-tree", this.$el).data(
        "kendoTreeList"
      );
      let $rows = this.$("tr.k-treelist-group", treeList.tbody);
      this.$.each($rows, (idx, row) => {
        if (expansion) {
          treeList.expand(row);
        } else {
          treeList.collapse(row);
        }
      });
      this.saveTreeState();
      this.addClassToRow(treeList);
    },

    // At editor close, update the value in treeList, and reset editedItem and editionRoute
    updateAtEditorClose(newValue) {
      setTimeout(() => {
        if (newValue) {
          this.editedItem.set("value", newValue);
          this.editedItem.set("forUser", true);
        }

        this.editedItem = null;
        this.editRoute = "";
      }, 300);
    },

    // Save the current user parameters tree state to localStorage
    saveTreeState() {
      // setTimeout(function, 0) to add CSS classes when all DOM content has been updated
      setTimeout(() => {
        let treeState = [];
        let treeList = this.$(".user-parameters-tree", this.$el).data(
          "kendoTreeList"
        );
        let items = treeList.items();
        items.each((index, item) => {
          if (this.$(item).attr("aria-expanded") === "true") {
            treeState.push(index);
          }
        });
        window.localStorage.setItem(
          "admin.user-parameters.treeState",
          JSON.stringify(treeState)
        );
      }, 0);
    },

    // Restore the user parameters tree state from localStorage, if it exists
    restoreTreeState() {
      let treeState = window.localStorage.getItem(
        "admin.user-parameters.treeState"
      );
      if (treeState) {
        let treeList = this.$(".user-parameters-tree", this.$el).data(
          "kendoTreeList"
        );
        let $rows = this.$("tr", treeList.tbody);
        this.$.each($rows, (idx, row) => {
          if (treeState.includes(idx)) {
            treeList.expand(row);
          } else {
            treeList.collapse(row);
          }
        });
        this.addClassToRow(treeList);
      } else {
        this.expand(true);
      }
    },

    // Empty the value of the search input
    clearSearchInput() {
      this.$(".user-search-input", this.$el).val("");
    },

    // Destroy the parameter editor if it exists and emit event to display System parameters
    switchParameters() {
      this.destroyEditor();
      this.$emit("switchParameters");
    },

    // Resize users tree
    resizeUsersGrid() {
      let $usersGrid = this.$(".users-grid", this.$el);
      let kUsersGrid = $usersGrid.data("kendoGrid");
      if (kUsersGrid) {
        $usersGrid.height(
          this.$(window).height() - $usersGrid.offset().top - 4
        );
        kUsersGrid.resize();
      }
    },

    // Resize user parameters tree
    resizeUserParametersTree() {
      let $tree = this.$(".user-parameters-tree", this.$el);
      let kTree = $tree.data("kendoTreeList");
      if (kTree) {
        $tree.height(this.$(window).height() - $tree.offset().top - 4);
        kTree.resize();
      }
    },

    // Destroy kendo components to free memory
    destroyKendoComponents() {
      this.destroyWindows();

      if (this.parametersTreeList) {
        this.parametersTreeList.destroy();
      }
      if (this.usersGrid) {
        this.usersGrid.destroy();
      }

      this.destroyEditor();
    },

    // Destroy editor component
    destroyEditor() {
      let editor = this.$(".edition-window").data("kendoWindow");
      if (editor) {
        editor.destroy();
      }
    },

    // Destroy kendo windows
    destroyWindows() {
      if (this.deleteConfirmationWindow) {
        this.deleteConfirmationWindow.destroy();
      }
      if (this.deleteErrorWindow) {
        this.deleteErrorWindow.destroy();
      }
    }
  },

  computed: {
    // Used in template to enable/disable the search input
    isSearchButtonDisabled() {
      return this.inputSearchValue === "";
    }
  },

  beforeDestroy() {
    this.destroyKendoComponents();
  },

  mounted() {
    // Init treeList to display users
    this.initUsersGrid();
    this.resizeUsersGrid();

    // Init treeList to display user parameters
    this.initTreeList();

    // Init switch button
    this.$(".switch-parameters", this.$el).kendoButton({
      icon: "arrow-left"
    });

    // Hide user parameters tree to show user search
    this.$(".parameters-div", this.$el).attr(
      "style",
      "display: none !important;"
    );

    // Focus on input for quick search
    this.$(".user-search-input", this.$el).focus();

    // Add event listener on treeList to expand/collapse rows on clic
    // And remove mousedown event listener to prevent double expand/collapse at click on arrows pf treeList
    this.$(".user-parameters-tree", this.$el)
      .off("mousedown")
      .on("mouseup", "tbody > .grid-expandable", e => {
        let treeList = this.parametersTree;
        if (this.$(e.currentTarget).attr("aria-expanded") === "false") {
          treeList.expand(e.currentTarget);
        } else {
          treeList.collapse(e.currentTarget);
        }

        this.addClassToRow(treeList);
        this.saveTreeState();
      });

    // At window resize, resize the treeLists
    window.addEventListener("resize", () => {
      this.resizeUsersGrid();
      this.resizeUserParametersTree();
    });
  }
};