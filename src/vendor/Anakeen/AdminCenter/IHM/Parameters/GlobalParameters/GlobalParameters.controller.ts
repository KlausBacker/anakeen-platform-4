import Vue from "vue";
import Component from "vue-class-component";
const parameterEditor = () => import("../ParameterEditor/ParameterEditor.vue");

declare var $;
declare var kendo;

@Component({
  components: {
    "admin-center-parameter-editor": parameterEditor
  },
  name: "admin-center-global-parameters"
})
export default class GlobalParametersController extends Vue {
  // Verify if the value of the parameter is a Json
  public static isJson(value) {
    try {
      JSON.parse(value);
      return true;
    } catch (e) {
      return false;
    }
  }

  // Destroy editor component
  public static destroyEditor() {
    const editor = $(".edition-window").data("kendoWindow");
    if (editor) {
      editor.destroy();
    }
  }
  // Data source for system parameters
  public allParametersDataSource = new kendo.data.TreeListDataSource({
    schema: {
      data: response => response.data.data
    },
    transport: {
      read: options => {
        this.$http
          .get("api/v2/admin/parameters/")
          .then(options.success)
          .catch(options.error);
      }
    }
  });
  // Current edited item to pass to the parameter editor
  public editedItem: any = null;

  // Current edition route to pass to the parameter editor
  public editRoute: string = "";

  // kendo components
  public parametersTree: any = null;
  public valueDisplayer: any = null;
  // METHODS ////////////////////////////////////
  // Init system parameters treeList with toolbar
  public initTreeList() {
    const toolbarTemplate = `
        <div class="global-parameters-toolbar">
            <span>&nbspSystem&nbsp</span>
            <label class="switch">
              <input type="checkbox" class="switch-btn">
              <span class="slider round"></span>
            </label>
            <span>&nbspUser&nbsp</span>
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

    // class to add to the treeList headers to display a filter icon showing filtered columns
    const headerAttributes = { class: "filterable-header" }; // jscs:ignore disallowQuotedKeysInObjects
    const tree = $(".parameters-tree", this.$el)
      .kendoTreeList({
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
            headerTemplate: '<a class="column-title">System value</a>'
          },
          {
            filterable: false,
            // Add a button only if the parameter is modifiable
            template:
              "# if (!data.rowLevel && !data.isStatic && !data.isReadOnly) { #" +
              '<a class="edition-btn" title="Edit"></a>' +
              "# } else if (!data.rowLevel) { #" +
              '<a class="display-btn" title="Show value"></a>' +
              "# } #",
            width: "6rem"
          }
        ],
        dataSource: this.allParametersDataSource,

        // Disable kendo column filters => global filter in toolbar
        filterable: false,
        height: "100%",
        resizable: false,
        toolbar: toolbarTemplate,

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

          // Init kendo buttons in treeList when new data is fetched from server
          $(".edition-btn", this.$el).kendoButton({
            icon: "edit"
          });

          $(".display-btn", this.$el).kendoButton({
            icon: "zoom"
          });
        }
      })
      .on("click", ".edition-btn", e => {
        // Open editor with the dataItem of the edited row
        const treeList = this.parametersTree;
        const dataItem = treeList.dataItem(e.currentTarget);
        this.openEditor(dataItem);
      })
      .on("click", ".display-btn", e => {
        const treeList = this.parametersTree;
        const dataItem = treeList.dataItem(e.currentTarget);
        this.displayValue(dataItem);
      })
      .on("click", ".switch-btn", () => this.switchParameters())
      .on("click", ".refresh-btn", () => {
        kendo.ui.progress($(".parameters-tree", this.$el), true);
        this.allParametersDataSource
          .read()
          .then(() => {
            kendo.ui.progress($(".parameters-tree", this.$el), false);
          })
          .catch(() => {
            kendo.ui.progress($(".parameters-tree", this.$el), false);
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
      });

    this.parametersTree = tree.data("kendoTreeList");

    // Init kendoButtons
    $(".switch-btn", this.$el).kendoButton({
      icon: "arrow-right"
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

  // Open editor window, passing the editedItem and the edition route url
  public openEditor(dataItem) {
    this.editedItem = dataItem;
    this.editRoute =
      "api/v2/admin/parameters/" +
      this.editedItem.nameSpace +
      "/" +
      this.editedItem.name +
      "/";
  }

  // Open a window displaying the entire value
  public displayValue(dataItem) {
    const displayedValue = dataItem.value
      ? dataItem.value
      : "[no value for this parameter]";

    let template;
    if (dataItem.value && GlobalParametersController.isJson(dataItem.value)) {
      template =
        '<pre class="value-displayer-content">' +
        JSON.stringify(JSON.parse(displayedValue), null, 5) +
        "</pre>";
    } else {
      template =
        '<p class="value-displayer-content">' + displayedValue + "</p>";
    }
    this.valueDisplayer = $(".value-displayer")
      .kendoWindow({
        actions: ["close"],
        content: {
          template
        },
        draggable: false,
        maxHeight: "80%",
        maxWidth: "80%",
        modal: true,
        open: () =>
          $(".value-displayer")
            .data("kendoWindow")
            .title("Value of " + dataItem.name)
            .center(),
        resizable: false,
        visible: false
      })
      .data("kendoWindow");

    this.valueDisplayer.center().open();
  }

  // Filter name, description and value columns
  public searchParameters(researchTerms) {
    if (researchTerms) {
      this.allParametersDataSource.filter({
        filters: [
          {
            field: "name",
            operator: "contains",
            value: researchTerms
          },
          {
            field: "description",
            operator: "contains",
            value: researchTerms
          },
          {
            field: "value",
            operator: "contains",
            value: researchTerms
          }
        ],
        logic: "or"
      });

      // Add icon to show filter effect to the user
      if (!$(".filterable-header", this.$el).children(".filter-icon").length) {
        $(".filterable-header", this.$el).prepend(
          $('<i class="material-icons filter-icon">filter_list</i>')
        );
      }

      // Expand treeList to show results
      this.expand(true);
    } else {
      // Reset filter with an empty filter
      this.allParametersDataSource.filter({});

      // Remove filter icon when nothing is filtered
      $(".filterable-header", this.$el)
        .children(".filter-icon")
        .remove();
    }
  }

  // Add a class to first and second level rows, to add custom CSS
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

  // Expand or collapse all rows of treeList (true => expand / false => collapse)
  public expand(expansion) {
    const treeList = this.parametersTree;
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

  // When editor is closed, update modified value, and reset editedItem and editedRoute
  public updateAtEditorClose(newValue) {
    setTimeout(() => {
      if (newValue) {
        this.editedItem.set("value", newValue);
      }

      this.editedItem = null;
      this.editRoute = "";
    }, 300);
  }

  // Save tree state (expanded and collapŝed rows) into localStorage
  public saveTreeState() {
    // setTimeout(function, 0) to save state when all DOM content has been updated
    setTimeout(() => {
      const treeState = [];
      const treeList = this.parametersTree;
      const items = treeList.items();
      items.each((index, item) => {
        if ($(item).attr("aria-expanded") === "true") {
          treeState.push(index);
        }
      });
      window.localStorage.setItem(
        "admin.parameters.treeState",
        JSON.stringify(treeState)
      );
    }, 0);
  }

  // Restore saved tree state (expanded and collapsed rows) from localStorage
  public restoreTreeState() {
    const treeState = window.localStorage.getItem("admin.parameters.treeState");
    if (treeState) {
      const treeList = this.parametersTree;
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

  // Destroy editor to prevent conflicts with others editors and send event to show user parameters
  public switchParameters() {
    GlobalParametersController.destroyEditor();
    this.$emit("switchParameters");
  }

  // Resize tree to fit the window
  public resizeTree() {
    const $tree = $(".parameters-tree", this.$el);
    const kTree = $tree.data("kendoTreeList");
    if (kTree) {
      $tree.height($(window).height() - $tree.offset().top - 4);
      kTree.resize();
    }
  }

  // Destroy all Kendo components to free memory
  public destroyKendoComponents() {
    if (this.valueDisplayer) {
      this.valueDisplayer.destroy();
    }
    if (this.parametersTree) {
      this.parametersTree.destroy();
    }

    GlobalParametersController.destroyEditor();
  }
  public beforeDestroy() {
    this.destroyKendoComponents();
  }
  public mounted() {
    // Init treeList and restore its state
    this.initTreeList();
    this.restoreTreeState();

    // Focus on filter input
    $(".global-search-input", this.$el).focus();

    // Add event listener on treeList to expand/collapse rows on click
    // and remove mousedown event listerner to prevent double expand/collapse at click on arrows of treeList
    $(".parameters-tree", this.$el)
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

    // At window resize, resize the tree list to fit the window
    window.addEventListener("resize", () => this.resizeTree());
  }
}
