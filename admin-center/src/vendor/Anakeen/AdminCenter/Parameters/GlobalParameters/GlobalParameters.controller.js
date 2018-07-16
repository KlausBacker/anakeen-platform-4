import parameterEditor from "../ParameterEditor/ParameterEditor.vue";

export default {
  name: "admin-center-global-parameters",

  components: {
    "admin-center-parameter-editor": parameterEditor
  },

  data() {
    return {
      // Data source for system parameters
      allParametersDataSource: new kendo.data.TreeListDataSource({
        transport: {
          read: {
            url: "/api/v2/admin/parameters/"
          }
        },
        schema: {
          data: "data"
        }
      }),

      // Current edited item to pass to the parameter editor
      editedItem: null,

      // Current edition route to pass to the parameter editor
      editRoute: ""
    };
  },

  methods: {
    // Init system parameters treeList with toolbar
    initTreeList() {
      let toolbarTemplate = `
        <div class="global-parameters-toolbar">
            <a class="switch-btn">User parameters</a>
            <a class="refresh-btn"></a>
            <a class="expand-btn"></a>
            <a class="collapse-btn"></a>
            <div class="input-group">
                <input type="text"
                       class="form-control global-search-input"
                       placeholder="Filter parameters..."
                       style="border-radius: .25rem;">
                <i class="input-group-addon material-icons reset-search-btn parameter-search-reset">close</i>
             </div>
        </div>
        `;

      // class to add to the treeList headers to display a filter icon showing filtered columns
      let headerAttributes = { class: "filterable-header" }; // jscs:ignore disallowQuotedKeysInObjects

      this.$(".parameters-tree", this.$el)
        .kendoTreeList({
          dataSource: this.allParametersDataSource,
          columns: [
            {
              field: "name",
              title: "Name",
              headerAttributes: headerAttributes
            },
            {
              field: "description",
              title: "Description",
              headerAttributes: headerAttributes
            },
            {
              field: "value",
              title: "System value",
              headerAttributes: headerAttributes
            },
            {
              width: "6rem",
              filterable: false,

              // Add a button only if the parameter is modifiable
              template:
                "# if (!data.rowLevel && !data.isStatic && !data.isReadOnly) { #" +
                '<a class="edition-btn" title="Edit"></a>' +
                "# } else if (!data.rowLevel) { #" +
                '<a class="display-btn" title="Show value"></a>' +
                "# } #"
            }
          ],

          // Disable kendo column filters => global filter in toolbar
          filterable: false,
          toolbar: toolbarTemplate,
          resizable: false,

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
            this.$(".edition-btn", this.$el).kendoButton({
              icon: "edit"
            });

            this.$(".display-btn", this.$el).kendoButton({
              icon: "zoom"
            });
          }
        })
        .on("click", ".edition-btn", e => {
          // Open editor with the dataItem of the edited row
          let treeList = this.$(e.delegateTarget).data("kendoTreeList");
          let dataItem = treeList.dataItem(e.currentTarget);
          this.openEditor(dataItem);
        })
        .on("click", ".display-btn", e => {
          let treeList = this.$(e.delegateTarget).data("kendoTreeList");
          let dataItem = treeList.dataItem(e.currentTarget);
          this.displayValue(dataItem);
        })
        .on("click", ".switch-btn", () => this.switchParameters())
        .on("click", ".refresh-btn", () => this.allParametersDataSource.read())
        .on("click", ".expand-btn", () => this.expand(true))
        .on("click", ".collapse-btn", () => this.expand(false))
        .on("input", ".global-search-input", e =>
          this.searchParameters(e.currentTarget.value)
        )
        .on("click", ".reset-search-btn", () => {
          this.$(".global-search-input", this.$el).val("");
          this.searchParameters("");
        });

      // Init kendoButtons
      this.$(".switch-btn", this.$el).kendoButton({
        icon: "arrow-right"
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

    // Open editor window, passing the editedItem and the edition route url
    openEditor(dataItem) {
      this.editedItem = dataItem;
      this.editRoute =
        "admin/parameters/" +
        this.editedItem.nameSpace +
        "/" +
        this.editedItem.name +
        "/";
    },

    // Open a window displaying the entire value
    displayValue(dataItem) {
      let displayedValue = dataItem.value
        ? dataItem.value
        : "[no value for this parameter]";

      let template;
      if (dataItem.value && dataItem.type === "json") {
        template =
          '<pre class="value-displayer-content">' +
          JSON.stringify(JSON.parse(displayedValue), null, 2) +
          "</pre>";
      } else {
        template =
          '<p class="value-displayer-content">' + displayedValue + "</p>";
      }
      this.$(".value-displayer")
        .kendoWindow({
          modal: true,
          draggable: false,
          resizable: false,
          maxWidth: "80%",
          visible: false,
          actions: ["close"],
          maxHeight: "80%",

          content: {
            template: template
          },

          open: () =>
            this.$(".value-displayer")
              .data("kendoWindow")
              .title("Value of " + dataItem.name)
              .center()
        })
        .data("kendoWindow")
        .center()
        .open();
    },

    // Filter name, description and value columns
    searchParameters(researchTerms) {
      if (researchTerms) {
        this.allParametersDataSource.filter({
          logic: "or",
          filters: [
            { field: "name", operator: "contains", value: researchTerms },
            {
              field: "description",
              operator: "contains",
              value: researchTerms
            },
            { field: "value", operator: "contains", value: researchTerms }
          ]
        });

        // Add icon to show filter effect to the user
        if (
          !this.$(".filterable-header", this.$el).children(".filter-icon")
            .length
        ) {
          this.$(".filterable-header", this.$el).append(
            this.$('<i class="material-icons filter-icon">filter_list</i>')
          );
        }

        // Expand treeList to show results
        this.expand(true);
      } else {
        // Reset filter with an empty filter
        this.allParametersDataSource.filter({});

        // Remove filter icon when nothing is filtered
        this.$(".filterable-header", this.$el)
          .children(".filter-icon")
          .remove();
      }
    },

    // Add a class to first and second level rows, to add custom CSS
    addClassToRow(treeList) {
      let items = treeList.items();
      const _vueThis = this;

      // setTimeout(function, 0) to add CSS classes when all DOM content has been updated
      setTimeout(() => {
        items.each(function addTypeClass() {
          let dataItem = treeList.dataItem(this);
          if (dataItem.rowLevel) {
            _vueThis
              .$(this)
              .addClass("grid-expandable grid-level-" + dataItem.rowLevel);
          }
        });
      }, 0);
    },

    // Expand or collapse all rows of treeList (true => expand / false => collapse)
    expand(expansion) {
      let treeList = this.$(".parameters-tree", this.$el).data("kendoTreeList");
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

    // When editor is closed, update modified value, and reset editedItem and editedRoute
    updateAtEditorClose(newValue) {
      setTimeout(() => {
        if (newValue) {
          this.editedItem.set("value", newValue);
        }

        this.editedItem = null;
        this.editRoute = "";
      }, 300);
    },

    // Save tree state (expanded and collapŝed rows) into localStorage
    saveTreeState() {
      // setTimeout(function, 0) to save state when all DOM content has been updated
      setTimeout(() => {
        let treeState = [];
        let treeList = this.$(".parameters-tree", this.$el).data(
          "kendoTreeList"
        );
        let items = treeList.items();
        items.each((index, item) => {
          if (this.$(item).attr("aria-expanded") === "true") {
            treeState.push(index);
          }
        });
        window.localStorage.setItem(
          "admin.parameters.treeState",
          JSON.stringify(treeState)
        );
      }, 0);
    },

    // Restore saved tree state (expanded and collapsed rows) from localStorage
    restoreTreeState() {
      let treeState = window.localStorage.getItem("admin.parameters.treeState");
      if (treeState) {
        let treeList = this.$(".parameters-tree", this.$el).data(
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
      }
    },

    // Destroy editor to prevent conficts with others editors and send event to show user parameters
    switchParameters() {
      let editor = this.$(".edition-window").data("kendoWindow");
      if (editor) {
        editor.destroy();
      }

      this.$emit("switchParameters");
    },

    // Resize tree to fit the window
    resizeTree() {
      let $tree = this.$(".parameters-tree", this.$el);
      let kTree = $tree.data("kendoTreeList");
      if (kTree) {
        $tree.height(this.$(window).height() - $tree.offset().top - 4);
        kTree.resize();
      }
    }
  },

  mounted() {
    // Init treeList and restore its state
    this.initTreeList();
    this.restoreTreeState();

    // Focus on filter input
    this.$(".global-search-input", this.$el).focus();

    // Add event listener on treeList to expand/collapse rows on click
    // and remove mousedown event listerner to prevent double expand/collapse at click on arrows of treeList
    this.$(".parameters-tree", this.$el)
      .off("mousedown")
      .on("mouseup", "tbody > .grid-expandable", e => {
        let treeList = this.$(e.delegateTarget).data("kendoTreeList");
        if (this.$(e.currentTarget).attr("aria-expanded") === "false") {
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
};
