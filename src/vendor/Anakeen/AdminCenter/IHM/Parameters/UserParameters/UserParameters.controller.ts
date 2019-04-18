import Vue from "vue";
import Component from "vue-class-component";
import { Prop } from "vue-property-decorator";
const parameterEditor = () => import("../ParameterEditor/ParameterEditor.vue");

declare var $;
declare var kendo;

@Component({
  components: {
    "admin-center-parameter-editor": parameterEditor
  },
  name: "admin-center-user-parameters"
})
export default class UserParametersController extends Vue {
  // Used in template to enable/disable the search input
  get isSearchButtonDisabled() {
    return this.inputSearchValue === "";
  }

  // Destroy editor component
  public static destroyEditor() {
    const editor = $(".edition-window").data("kendoWindow");
    if (editor) {
      editor.destroy();
    }
  }
  @Prop({ type: Boolean }) public globalParameters;
  // Data source for user parameters treeList
  public userParametersDataSource: IuserParametersDataSource;

  // Current edited item and route url to modify it
  public editedItem: any = null;
  public editRoute: string = "";

  // Login of the selected user
  public actualLogin: string = "";

  // Value entered in the search input
  public inputSearchValue: string = "";

  // Memorize kendo components
  public deleteConfirmationWindow: any = null;
  public deleteErrorWindow: any = null;
  public usersGrid: any = null;
  public parametersTreeList: any = null;
  public parametersTree: any = null;
  // Init the grid containing users
  public initUsersGrid() {
    this.usersGrid = $(".users-grid", this.$el)
      .kendoGrid({
        columns: [
          { field: "login", title: "Login" },
          { field: "firstname", title: "First name" },
          { field: "lastname", title: "Last name" },
          {
            filterable: false,
            // Button to select the user and access his parameters
            template: '<a class="selection-btn">Select user</a>',
            width: "14rem"
          }
        ],
        dataBound: () => {
          // Init kendoButtons when users are loaded from the server
          $(".selection-btn", this.$el).kendoButton({
            icon: "user"
          });
          this.restoreTreeState();
        },

        // Datasource set to display the users in a grid
        dataSource: {
          pageSize: 10,
          schema: {
            data: response => response.data.data.users,
            total: response => response.data.data.total
          },
          serverPaging: true,
          transport: {
            read: options => {
              this.$http
                .get("api/v2/admin/parameters/users/")
                .then(options.success)
                .catch(options.error);
            }
          }
        },

        // Disable columns filters to add global filter
        filterable: false,
        messages: {
          noRows: "Search a user to modify his settings"
        },
        pageable: {
          pageSize: 10
        },
        resizable: false,
        selectable: "rows"
      })
      .on("click", ".selection-btn", e => {
        // Select a user to display his parameters with the data item
        const grid = this.usersGrid;
        grid.select(e.currentTarget.parentNode.parentNode);
        const dataItem = grid.dataItem(grid.select());
        this.selectUser(dataItem);
        grid.clearSelection();
      })
      .data("kendoGrid");
  }

  // Init the treeList containing all the parameters for the selected user
  public initTreeList() {
    // Custom toolbar template to add a global filter
    const toolbarTemplate = `
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
    const headerAttributes = { class: "user-filterable-header" }; // jscs:ignore disallowQuotedKeysInObjects

    this.parametersTree = $(".user-parameters-tree", this.$el)
      .kendoTreeList({
        collapse: e => {
          this.addClassToRow(e.sender);
          this.saveTreeState();
        },
        columns: [
          {
            field: "name",
            headerAttributes,
            headerTemplate: '<a class="column-title">Name</a>'
          },
          {
            field: "description",
            headerAttributes,
            headerTemplate: '<a class="column-title">Description</a>'
          },
          {
            field: "value",
            headerAttributes,
            headerTemplate: '<a class="column-title">User value</a>'
          },
          {
            field: "initialValue",
            headerAttributes,
            headerTemplate: '<a class="column-title">System value</a>'
          },
          {
            filterable: false,
            // and restore/delete button on user defined parameters
            template:
              "# if (!data.rowLevel && !data.isStatic && !data.isReadOnly) { #" +
              '<a class="edition-btn" title="Edit"></a>' +
              "# if (data.forUser) { #" +
              '<a class="delete-btn" title="Restore system value"></a>' +
              "# } #" +
              "# } #",
            // Display edition button on modifiable parameters
            width: "10rem"
          }
        ],
        dataBound: e => {
          this.addClassToRow(e.sender);
          this.restoreTreeState();

          // Init kendoButtons in tree
          $(".edition-btn", this.$el).kendoButton({
            icon: "edit"
          });
          $(".delete-btn", this.$el).kendoButton({
            icon: "undo"
          });
        },
        dataSource: this.userParametersDataSource,
        expand: e => {
          this.addClassToRow(e.sender);
          this.saveTreeState();
        },
        // Disable filter on columns to add a global filter
        filterable: false,
        maxHeight: "100%",
        maxWidth: "100%",
        toolbar: toolbarTemplate,

        resizable: false
      })
      .on("click", ".edition-btn", e => {
        // Open parameter editor with selected dataItem
        const treeList = this.parametersTree;
        const dataItem = treeList.dataItem(e.currentTarget);
        this.openEditor(dataItem);
      })
      .on("click", ".delete-btn", e => {
        // Delete/Restore user parameter with selected dataItem
        const treeList = this.parametersTree;
        const dataItem = treeList.dataItem(e.currentTarget);
        this.deleteParameter(dataItem);
      })
      .on("click", ".back-btn", () => {
        // Reset actual login when returning to user selection
        this.actualLogin = "";

        // Display user search
        $(".user-search", this.$el).css("display", "");
        $(".parameters-div", this.$el).attr(
          "style",
          (i, s) => s + "display: none !important;"
        );

        // Focus on search input
        $(".user-search-input", this.$el).focus();

        // Resize users grid when it is displayed
        this.resizeUsersGrid();
      })
      .on("click", ".refresh-btn", () => {
        // Re-fetch data from server
        kendo.ui.progress($(".user-parameters-tree", this.$el), true);
        this.userParametersDataSource
          .read()
          .then(() => {
            kendo.ui.progress($(".user-parameters-tree", this.$el), false);
          })
          .catch(() => {
            kendo.ui.progress($(".user-parameters-tree", this.$el), false);
            document.querySelector(".ank-notifier").dispatchEvent(
              new CustomEvent("ankNotification", {
                detail: [
                  {
                    content: {
                      textContent: "Loading of parameters from server failed",
                      title: "Parameters loading failed"
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
        this.searchParameters($(".global-search-input", this.$el).val())
      )
      .on("keyup", ".global-search-input", e => {
        if (e.key === "Enter") {
          this.searchParameters($(".global-search-input", this.$el).val());
        }
      })
      .on("click", ".reset-search-btn", () => {
        $(".global-search-input", this.$el).val("");
        this.searchParameters("");
      })
      .data("kendoTreeList");

    // Init kendoButtons of toolbar
    $(".back-btn", this.$el).kendoButton({
      icon: "arrow-chevron-left"
    });
    $(".refresh-btn", this.$el).kendoButton({
      icon: "reload"
    });
    $(".expand-btn", this.$el).kendoButton({
      icon: "arrow-60-down"
    });
    $(".collapse-btn", this.$el).kendoButton({
      icon: "arrow-60-up"
    });
  }

  // Select a user in users grid and display his parameters
  public selectUser(dataItem) {
    // Set new DataSource
    this.actualLogin = dataItem.login;
    this.userParametersDataSource = new kendo.data.TreeListDataSource({
      schema: {
        data: response => response.data.data
      },
      transport: {
        read: options => {
          this.$http
            .get("api/v2/admin/parameters/users/" + this.actualLogin + "/")
            .then(options.success)
            .catch(options.error);
        }
      }
    });
    this.parametersTree.setDataSource(this.userParametersDataSource);

    // Display parameters and hide user search
    $(".user-search", this.$el).css("display", "none");
    $(".parameters-div", this.$el).css("display", "");

    // Resize user parameters treeList when displaying it
    this.resizeUserParametersTree();

    // Focus on filter input
    $(".global-search-input", this.$el).focus();
  }

  // Open the parameter editor with the correct dataItem and modification route url
  public openEditor(dataItem) {
    this.editedItem = dataItem;
    this.editRoute =
      "api/v2/admin/parameters/" +
      this.actualLogin +
      "/" +
      dataItem.nameSpace +
      "/" +
      dataItem.name +
      "/";
  }

  // Send a request to the server to remove the user definition of the passed parameter
  // to restore the system value of this parameter for the user
  public deleteParameter(dataItem) {
    this.$http
      .delete(
        "api/v2/admin/parameters/" +
          this.actualLogin +
          "/" +
          dataItem.nameSpace +
          "/" +
          dataItem.name +
          "/"
      )
      .then(() => {
        // Show a confirmation window to notify the user of the modification
        this.deleteConfirmationWindow = $(".delete-confirmation-window")
          .kendoWindow({
            actions: [],
            draggable: false,
            modal: true,
            resizable: false,
            title: "Parameter restored",
            visible: false,
            width: "30%"
          })
          .data("kendoWindow");

        this.deleteConfirmationWindow.center().open();

        // Init the confirmation window's close kendoButton
        $(".delete-confirmation-btn").kendoButton({
          icon: "arrow-chevron-left"
        });

        // Re-fetch the parameters from server to display the updated values
        this.userParametersDataSource.read();
      })
      .catch(() => {
        // Show an error window to notify the user that the parameter restoration failed
        this.deleteErrorWindow = $(".delete-error-window")
          .kendoWindow({
            actions: [],
            draggable: false,
            modal: true,
            resizable: false,
            title: "Error",
            visible: false,
            width: "30%"
          })
          .data("kendoWindow");

        this.deleteErrorWindow.center().open();

        // Init error window's close kendoButton
        $(".delete-error-btn").kendoButton({
          icon: "arrow-chevron-left"
        });
      });
  }

  // Close restoration confirmation window
  public closeDeleteConfirmation() {
    this.deleteConfirmationWindow.close();
  }

  // Close restoration error window
  public closeDeleteError() {
    this.deleteErrorWindow.close();
  }

  // Search a user on the server in users treeList
  public searchUser() {
    const user = $(".user-search-input", this.$el).val();
    if (user.trim()) {
      const usersDataSource = new kendo.data.DataSource({
        pageSize: 10,
        schema: {
          data: response => response.data.data.users,
          total: response => response.data.data.total
        },
        serverPaging: true,
        transport: {
          read: options => {
            this.$http
              .get("api/v2/admin/parameters/users/search/" + user + "/")
              .then(options.success)
              .catch(options.error);
          }
        }
      });
      this.usersGrid.setDataSource(usersDataSource);
    }
  }

  // Filter treeList parameters on name, description, value and initial system value
  public searchParameters(researchTerms) {
    if (researchTerms) {
      this.userParametersDataSource.filter({
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
        ],
        logic: "or"
      });

      // Add icon to show filter effect to the user
      if (
        !$(".user-filterable-header", this.$el).children(".filter-icon").length
      ) {
        $(".user-filterable-header", this.$el).prepend(
          $('<i class="material-icons filter-icon">filter_list</i>')
        );
      }

      // Expand treeList to display all results
      this.expand(true);
    } else {
      // Reset filter passing an empty one
      this.userParametersDataSource.filter({});

      // Remove filter icon when nothing is filtered
      $(".user-filterable-header", this.$el)
        .children(".filter-icon")
        .remove();
    }
  }

  // Add a class to level 1 and 2 rows of treeList, to add custom CSS
  public addClassToRow(treeList) {
    const items = treeList.items();

    // setTimeout(function, 0) to add CSS classes when all DOM content has been updated
    setTimeout(() => {
      items.each(function addTypeClass(this: any) {
        const dataItem = treeList.dataItem(this);
        if (dataItem.rowLevel) {
          $(this).addClass("grid-expandable grid-level-" + dataItem.rowLevel);
        }
      });
    }, 0);
  }

  // Expand/Collapse every rows of the user parameters tree list (true => expand / false => collapse)
  public expand(expansion) {
    const treeList = $(".user-parameters-tree", this.$el).data("kendoTreeList");
    const $rows = $("tr.k-treelist-group", treeList.tbody);
    $.each($rows, (idx, row) => {
      if (expansion) {
        treeList.expand(row);
      } else {
        treeList.collapse(row);
      }
    });
    this.saveTreeState();
    this.addClassToRow(treeList);
  }

  // At editor close, update the value in treeList, and reset editedItem and editionRoute
  public updateAtEditorClose(newValue) {
    setTimeout(() => {
      if (newValue) {
        this.editedItem.set("value", newValue);
        this.editedItem.set("forUser", true);
      }

      this.editedItem = null;
      this.editRoute = "";
    }, 300);
  }

  // Save the current user parameters tree state to localStorage
  public saveTreeState() {
    // setTimeout(function, 0) to add CSS classes when all DOM content has been updated
    setTimeout(() => {
      const treeState = [];
      const treeList = $(".user-parameters-tree", this.$el).data(
        "kendoTreeList"
      );
      const items = treeList.items();
      items.each((index, item) => {
        if ($(item).attr("aria-expanded") === "true") {
          treeState.push(index);
        }
      });
      window.localStorage.setItem(
        "admin.user-parameters.treeState",
        JSON.stringify(treeState)
      );
    }, 0);
  }

  // Restore the user parameters tree state from localStorage, if it exists
  public restoreTreeState() {
    const treeState = window.localStorage.getItem(
      "admin.user-parameters.treeState"
    );
    if (treeState) {
      const treeList = $(".user-parameters-tree", this.$el).data(
        "kendoTreeList"
      );
      const $rows = $("tr", treeList.tbody);
      $.each($rows, (idx, row) => {
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
  }

  // Empty the value of the search input
  public clearSearchInput() {
    $(".user-search-input", this.$el).val("");
  }

  // Destroy the parameter editor if it exists and emit event to display System parameters
  public switchParameters() {
    UserParametersController.destroyEditor();
    this.$emit("switchParameters");
  }

  // Resize users tree
  public resizeUsersGrid() {
    const $usersGrid = $(".users-grid", this.$el);
    const kUsersGrid = $usersGrid.data("kendoGrid");
    if (kUsersGrid) {
      $usersGrid.height($(window).height() - $usersGrid.offset().top - 4);
      kUsersGrid.resize();
    }
  }

  // Resize user parameters tree
  public resizeUserParametersTree() {
    const $tree = $(".user-parameters-tree", this.$el);
    const kTree = $tree.data("kendoTreeList");
    if (kTree) {
      $tree.height($(window).height() - $tree.offset().top - 4);
      kTree.resize();
    }
  }

  // Destroy kendo components to free memory
  public destroyKendoComponents() {
    this.destroyWindows();

    if (this.parametersTreeList) {
      this.parametersTreeList.destroy();
    }
    if (this.usersGrid) {
      this.usersGrid.destroy();
    }

    UserParametersController.destroyEditor();
  }

  // Destroy kendo windows
  public destroyWindows() {
    if (this.deleteConfirmationWindow) {
      this.deleteConfirmationWindow.destroy();
    }
    if (this.deleteErrorWindow) {
      this.deleteErrorWindow.destroy();
    }
  }
  public beforeDestroy() {
    this.destroyKendoComponents();
  }
  public mounted() {
    // Init treeList to display users
    this.initUsersGrid();
    this.resizeUsersGrid();

    // Init treeList to display user parameters
    this.initTreeList();

    // Init switch button
    $(".switch-parameters", this.$el).prop("checked", !this.globalParameters);
    // Hide user parameters tree to show user search
    $(".parameters-div", this.$el).attr("style", "display: none !important;");

    // Focus on input for quick search
    $(".user-search-input", this.$el).focus();

    // Add event listener on treeList to expand/collapse rows on clic
    // And remove mousedown event listener to prevent double expand/collapse at click on arrows pf treeList
    $(".user-parameters-tree", this.$el)
      .off("mousedown")
      .on("mouseup", "tbody > .grid-expandable", e => {
        const treeList = this.parametersTree;
        if ($(e.currentTarget).attr("aria-expanded") === "false") {
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
}
