export default {
  name: "admin-center-list-routes",
  data() {
    return {
      allRoutesDataSource: new kendo.data.TreeListDataSource({
        transport: {
          read: "/api/v2/admin/routes/"
        },
        schema: {
          data: function(response) {
            return response.data;
          }
        },
        sort: { field: "rowLevel", dir: "asc" }
      }),
      allMiddlewareDataSource: new kendo.data.TreeListDataSource({
        transport: {
          read: "/api/v2/admin/middlewares/"
        },
        schema: {
          data: function(response) {
            return response.data;
          }
        }
      })
    };
  },
  methods: {
    initTreeList() {
      let refreshBtn = `
                <div class="routes-toolbar">
                    <a class="routeRefresh-btn"><a/>
                    <a class="routeExpand-btn"></a>
                    <a class="routeCollapse-btn"></a>
                </div>
            `;
      this.$(".routes-tab").select(
        this.$(".routes-tree")
          .kendoTreeList({
            dataSource: this.allRoutesDataSource,
            columns: [
              {
                field: "name",
                title: "Name",
                sortable: true,
                width: "15%"
              },
              {
                field: "method",
                title: "Method",
                width: "5%",
                sortable: false
              },
              {
                field: "pattern",
                title: "Pattern",
                sortable: true,
                width: "30%"
              },
              {
                field: "description",
                title: "Description",
                sortable: false,
                width: "25%"
              },
              {
                field: "priority",
                title: "Priority",
                filterable: false,
                sortable: false,
                width: "4%"
              },
              {
                field: "override",
                title: "Overrided",
                filterable: false,
                sortable: false,
                width: "5%"
              },
              {
                template:
                  "# if(data.rowLevel === 2) { #" +
                  '<div class="btn-group" role="group" aria-label="activation group button">' +
                  ' <button type="button" class="btn btn-outline-primary btn-sm activation-btn">Activated</button>' +
                  ' <button type="button" class="btn btn-outline-danger btn-sm deactivation-btn">Deactivated</button>' +
                  "</div>" +
                  "# } #",
                width: "9%",
                filterable: false,
                sortable: false
              }
            ],
            toolbar: refreshBtn,
            filterable: {
              extra: false,
              operators: {
                string: {
                  contains: "Contains..."
                }
              }
            },
            filter: e => {
              if (e.filter === null) {
                this.allRoutesDataSource.filter({});
              } else {
                this.expand(true, ".routes-tree");
              }
            },
            sortable: true,
            resizable: false,
            expand: e => {
              this.addClassToRow(e.sender);
              this.saveTreeState(".routes-tree");
            },
            collapse: e => {
              this.addClassToRow(e.sender);
              this.saveTreeState(".routes-tree");
            },
            dataBound: e => {
              this.addClassToRow(e.sender);
              this.restoreTreeState(".routes-tree");
              this.$(".activation-btn").on("click", event => {
                this.$(event.target).addClass("active");
                this.$(event.target).siblings(".deactivation-btn").removeClass("active");
                event.target.disabled = true;
                this.$(event.target)
                  .siblings(".deactivation-btn")
                  .prop("disabled", false);
                const sender = event.target.closest("tr[role=row]");
                const elt = this.allRoutesDataSource._data.find(
                  x => x.name === sender.firstElementChild.textContent
                );
                if (sender.className.includes("tree-level-2")) {
                  if (
                    this.allRoutesDataSource._data.find(
                      x => x.id === elt.parentId
                    )
                  ) {
                    // if the route has a namespace
                    const parentName = this.allRoutesDataSource._data.find(
                      x => x.id === elt.parentId
                    ).name;
                    this.activateRoute(
                      parentName + "::" + sender.firstElementChild.textContent,
                      elt
                    );
                  } else {
                    // if the route doesn't have any namespace
                    this.activateRoute(
                      sender.firstElementChild.textContent,
                      elt
                    );
                  }
                }
              });
              this.$(".deactivation-btn").on("click", event => {
                this.$(event.target).addClass("active");
                this.$(event.target).siblings(".activation-btn").removeClass("active");
                event.target.disabled = true;
                this.$(event.target)
                  .siblings(".activation-btn")
                  .prop("disabled", false);
                const sender = event.target.closest("tr[role=row]");
                const elt = this.allRoutesDataSource._data.find(
                  x => x.name === sender.firstElementChild.textContent
                );
                if (sender.className.includes("tree-level-2")) {
                  if (
                    this.allRoutesDataSource._data.find(
                      x => x.id === elt.parentId
                    )
                  ) {
                    // if the route has a namespace
                    const parentName = this.allRoutesDataSource._data.find(
                      x => x.id === elt.parentId
                    ).name;
                    this.deactivateRoute(
                      parentName + "::" + sender.firstElementChild.textContent,
                      elt
                    );
                  } else {
                    // if the route doesn't have any namespace
                    this.deactivateRoute(
                      sender.firstElementChild.textContent,
                      elt
                    );
                  }
                }
              });
              // activate/deactivate switch according to dataSource
              this.$(".activation-btn").each((index, item) => {
                let route = this.allRoutesDataSource._data.find(
                  x =>
                    x.name ===
                    item.closest("tr[role=row]").firstElementChild.textContent
                );
                item.disabled = route.active;
                if (route.active) {
                  this.$(item).addClass("active");
                }
              });
              this.$(".deactivation-btn").each((index, item) => {
                let route = this.allRoutesDataSource._data.find(
                  x =>
                    x.name ===
                    item.closest("tr[role=row]").firstElementChild.textContent
                );
                item.disabled = !route.active;
                if(!route.active) {
                  this.$(item).addClass("active");
                }
              });
              this.expand(true, ".routes-tree")
            }
          })
          .on("click", ".routeRefresh-btn", () => {
            kendo.ui.progress(this.$(".routes-tree"), true);
            this.allRoutesDataSource
              .read()
              .then(() => {
                kendo.ui.progress(this.$(".routes-tree"), false);
                this.$emit("ank-admin-notify", {
                  content: {
                    title: "Routes loading",
                    message: "Routes successfully loaded from server",
                  },
                  type: "admin-success"
                });
              })
              .catch(() => {
                kendo.ui.progress(this.$(".routes-tree"), false);
                this.$emit("ank-admin-notify", {
                  content: {
                    title: "Routes loading failed",
                    message: "Routes failed to load from server",
                  },
                  type: "admin-error"
                });
              });
          })
          .on("click", ".routeExpand-btn", () =>
            this.expand(true, ".routes-tree")
          )
          .on("click", ".routeCollapse-btn", () =>
            this.expand(false, ".routes-tree")
          ),
        this.$(".routeRefresh-btn").kendoButton({ icon: "reload" }),
        this.$(".routeExpand-btn").kendoButton({ icon: "arrow-60-down"}),
        this.$(".routeCollapse-btn").kendoButton({ icon: "arrow-60-up"})
      );
      this.$(".middlewares-tab").select(
        this.$(".middlewares-tree")
          .kendoTreeList({
            dataSource: this.allMiddlewareDataSource,
            columns: [
              {
                field: "name",
                title: "Name",
                sortable: true,
                width: "30%",
                filterable: true
              },
              {
                field: "method",
                title: "Method",
                width: "5%",
                filterable: true,
                sortable: false
              },
              {
                field: "pattern",
                title: "Pattern",
                width: "30%",
                filterable: true,
                sortable: true
              },
              {
                field: "description",
                title: "Description",
                width: "30%",
                filterable: true,
                sortable: false
              },
              {
                field: "priority",
                title: "Priority",
                width: "5%",
                filterable: false,
                sortable: false
              }
            ],
            toolbar: refreshBtn,
            filterable: {
              extra: false,
              operators: {
                string: {
                  contains: "Contains..."
                }
              }
            },
            filter: e => {
              if (e.filter === null) {
                this.allMiddlewareDataSource.filter({});
              } else {
                this.expand(true, ".middlewares-tree");
              }
            },
            sortable: true,
            resizable: false,
            expand: e => {
              this.addClassToRow(e.sender);
              this.saveTreeState(".middlewares-tree");
            },
            collapse: e => {
              this.addClassToRow(e.sender);
              this.saveTreeState(".middlewares-tree");
            },
            dataBound: e => {
              this.addClassToRow(e.sender);
              this.restoreTreeState(".middlewares-tree");
              this.expand(true, ".middlewares-tree");
            }
          })
          .on("click", ".routeRefresh-btn", () => {
            kendo.ui.progress(this.$(".middlewares-tree"), true);
            this.allMiddlewareDataSource
              .read()
              .then(() => {
                kendo.ui.progress(this.$(".middlewares-tree"), false);
                this.$emit("ank-admin-notify", {
                  content: {
                    title: "Middleware loading",
                    message: "Middlewares successfully loaded from server",
                  },
                  type: "admin-success"
                });
              })
              .catch(() => {
                kendo.ui.progress(this.$(".middlewares-tree"), false);
                this.$emit("ank-admin-notify", {
                  content: {
                    title: "Middlewares loading failed",
                    message: "Middlewares failed to load from server",
                  },
                  type: "admin-error"
                });
              })
          })
          .on("click", ".routeExpand-btn", () =>
            this.expand(true, ".middlewares-tree")
          )
          .on("click", ".routeCollapse-btn", () =>
            this.expand(false, ".middlewares-tree")
          ),
        this.$(".routeRefresh-btn").kendoButton({icon: "reload"}),
        this.$(".routeExpand-btn").kendoButton({icon: "arrow-60-down"}),
        this.$(".routeCollapse-btn").kendoButton({icon: "arrow-60-up"}),
      );
    },
    addClassToRow(treeList) {
      let items = treeList.items();
      const vueInstance = this;
      setTimeout(() => {
        items.each(function addTypeClass() {
          let dataItem = treeList.dataItem(this);
          if (dataItem.rowLevel) {
            vueInstance
              .$(this)
              .addClass(
                "tree-level-" + dataItem.rowLevel + " " + dataItem.name
              );
          }
        });
      }, 0);
    },
    expand(expansion, el) {
      let treeList = this.$(el).data("kendoTreeList");
      let $rows = this.$("tr.k-treelist-group", treeList.tbody);
      this.$.each($rows, (idx, row) => {
        expansion ? treeList.expand(row) : treeList.collapse(row);
      });
      this.saveTreeState(el);
      this.addClassToRow(treeList);
    },
    saveTreeState(el) {
      setTimeout(() => {
        let treeState = [];
        let treeList = this.$(el).data("kendoTreeList");
        let items = treeList.items();
        items.each((index, item) => {
          if (this.$(item).attr("aria-expanded") === "true")
            treeState.push(index);
        });
        window.localStorage.setItem("admin.routes.treeState", treeState);
      }, 0);
    },
    restoreTreeState(el) {
      let treeState = window.localStorage.getItem("admin.routes.treeState");
      if (treeState) {
        let treeList = this.$(el).data("kendoTreeList");
        let $rows = this.$("tr", treeList.tbody);
        this.$.each($rows, (idx, row) => {
          treeState.includes(idx)
            ? treeList.expand(row)
            : treeList.collapse(row);
        });
        this.addClassToRow(treeList);
      }
    },
    activateRoute(route, elt) {
      kendo.ui.progress(this.$(".routes-tree"), true);
      return this.$ankApi
        .post(encodeURI("admin/routes/" + route + "/activate/"))
        .then(response => {
          if (response.status === 200 && response.statusText === "OK") {
            elt.active = true;
            this.$emit("ank-admin-notify", {
              content: {
                title: "Route Activation",
                message: "The route " + elt.name + " has been activated\n",
              },
              type: "admin-success"
            });
            kendo.ui.progress(this.$(".routes-tree"), false);
          } else {
            this.$emit("ank-admin-notify", {
              content: {
                title: "Route Activation",
                message: "The route " + elt.name + " failed to be activated\n",
              },
              type: "admin-error"
            });
            kendo.ui.progress(this.$(".routes-tree"), false);
            throw new Error(response);
          }
        })
        .catch(error => {
          console.error("Unable to get options", error);
        });
    },
    deactivateRoute(route, elt) {
      kendo.ui.progress(this.$(".routes-tree"), true);
      return this.$ankApi
        .delete(encodeURI("admin/routes/" + route + "/deactivate/"))
        .then(response => {
          if (response.status === 200 && response.statusText === "OK") {
            elt.active = false;
            this.$emit("ank-admin-notify", {
              content: {
                title: "Route Deactivation",
                message: "The route " + elt.name + " has been deactivated\n",
              },
              type: "admin-success"
            });
            kendo.ui.progress(this.$(".routes-tree"), false);
          } else {
            this.$emit("ank-admin-notify", {
              content: {
                title: "Route Deactivation",
                message:
                  "The route " + elt.name + " failed to be deactivated\n",
              },
              type: "admin-error"
            });
            kendo.ui.progress(this.$(".routes-tree"), false);
            throw new Error(response);
          }
        })
        .catch(error => {
          console.error("Unable to get options", error);
        });
    }
  },
  mounted() {
    this.$(".tabstrip").kendoTabStrip({
      animation: {
        open: {
          effects: "fadeIn"
        }
      }
    });
    this.initTreeList();
    this.restoreTreeState(".routes-tree");
    this.restoreTreeState(".middlewares-tree");
    // Add event listener on treeList to expand/collapse rows on click
    // and remove mousedown event listener to prevent double expand/collapse at click on arrows of treeList
    this.$(".routes-tree")
      .off("mousedown")
      .on("mouseup", "tbody > .tree-level-1", e => {
        let treeList = this.$(e.delegateTarget).data("kendoTreeList");
        if (this.$(e.currentTarget).attr("aria-expanded") === "false") {
          treeList.expand(e.currentTarget);
        } else {
          treeList.collapse(e.currentTarget);
        }

        this.addClassToRow(treeList);
        this.saveTreeState(".routes-tree");
      });
    this.$(".middlewares-tree")
      .off("mousedown")
      .on("mouseup", "tbody > .tree-level-1", e => {
        let treeList = this.$(e.delegateTarget).data("kendoTreeList");
        if (this.$(e.currentTarget).attr("aria-expanded") === "false") {
          treeList.expand(e.currentTarget);
        } else {
          treeList.collapse(e.currentTarget);
        }

        this.addClassToRow(treeList);
        this.saveTreeState(".middlewares-tree");
      });
    window.addEventListener("resize", () => {
      let $tree = this.$(".routes-tree");
      let ktree = $tree.data("kendoTreeList");
      if (ktree) {
        $tree.height(this.$(window).height() - $tree.offset().top - 4);
        ktree.resize();
      }
    });
  }
};
