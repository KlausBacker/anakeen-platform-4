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
        }
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
                width: "30%"
              },
              {
                field: "priority",
                title: "Priority",
                filterable: false,
                sortable: false,
                width: "5%"
              },
              {
                field: "overrided",
                title: "Overrided",
                filterable: false,
                sortable: false,
                width: "5%"
              },
              {
                fiedl: "active",
                template:
                  '<input type="checkbox" class="activation-switch" aria-label="Activation Switch"/>',
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
            sortable: true,
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
              this.$(
                ".activation-switch:not(.activation-switch[data-role=switch])"
              ).kendoMobileSwitch({
                change: e => {
                  const sender = e.sender.element[0].closest("tr[role=row]");
                  if (sender.className.includes("tree-level-2")) {
                    const elt = this.allRoutesDataSource._data.find(
                      x => x.name === sender.firstElementChild.textContent
                    );
                    if (
                      this.allRoutesDataSource._data.find(
                        x => x.id === elt.parentId
                      )
                    ) {
                      // if the route has a namespace
                      const parentName = this.allRoutesDataSource._data.find(
                        x => x.id === elt.parentId
                      ).name;
                      if (e.checked) {
                        this.activateRoute(
                          parentName +
                            "::" +
                            sender.firstElementChild.textContent,
                          elt
                        );
                      } else if (!e.checked) {
                        this.deactivateRoute(
                          parentName +
                            "::" +
                            sender.firstElementChild.textContent,
                          elt
                        );
                      }
                    } else {
                      // if the route doesn't have any namespace
                      if (e.checked) {
                        this.activateRoute(
                          sender.firstElementChild.textContent,
                          elt
                        );
                      } else if (!e.checked) {
                        this.deactivateRoute(
                          sender.firstElementChild.textContent,
                          elt
                        );
                      }
                    }
                  } else {
                    const parent = this.allRoutesDataSource._data.find(
                      x => x.name === sender.firstElementChild.textContent
                    ).id;
                    const parentName = this.allRoutesDataSource._data.find(
                      x => x.id === parent
                    ).name;
                    this.allRoutesDataSource._data.forEach(elt => {
                      if (elt.parentId === parent) {
                        // if the element is a child of the namespace activate/deactivate following namespace's route
                        if (e.checked) {
                          this.activateRoute(parentName + "::" + elt.name, elt);
                        } else if (!e.checked) {
                          this.deactivateRoute(
                            parentName + "::" + elt.name,
                            elt
                          );
                        }
                      }
                    });
                  }
                }
              });
              // activate/deactivate switch according to dataSource
              // the point of this solution is to 'bind' kendo switch checked attribute
              // to kendo treeList dataSource
              this.$(".activation-switch").each((index, item) => {
                this.$(item)
                  .data("kendoMobileSwitch")
                  .check(this.allRoutesDataSource._data[index].active);
              });
            }
          })
          .on("click", ".routeRefresh-btn", () =>
            this.allRoutesDataSource.read()
          ),
        this.$(".routeRefresh-btn").kendoButton({ icon: "reload" })
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
            filterable: true,
            sortable: true,
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
            }
          })
          .on("click", ".refresh-btn", () =>
            this.allMiddlewareDataSource.read()
          )
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
    expand(expansion) {
      let treeList = this.$(".routes-tree").data("kendoTreeList");
      let $rows = this.$("tr.k-treelist-group", treeList.tbody);
      this.$.each($rows, (idx, row) => {
        expansion ? treeList.expand(row) : treeList.collapse(row);
      });
      this.saveTreeState();
      this.addClassToRow(treeList);
    },
    saveTreeState() {
      setTimeout(() => {
        let treeState = [];
        let treeList = this.$(".routes-tree").data("kendoTreeList");
        let items = treeList.items();
        items.each((index, item) => {
          if (this.$(item).attr("aria-expanded") === "true")
            treeState.push(index);
        });
        window.localStorage.setItem("admin.routes.treeState", treeState);
      }, 0);
    },
    restoreTreeState() {
      let treeState = window.localStorage.getItem("admin.routes.treeState");
      if (treeState) {
        let treeList = this.$(".routes-tree").data("kendoTreeList");
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
      return this.$ankApi
        .post(encodeURI("admin/routes/" + route + "/activate/"))
        .then(response => {
          if (response.status === 200 && response.statusText === "OK") {
            elt.active = true;
          } else {
            throw new Error(response);
          }
        })
        .catch(error => {
          console.error("Unable to get options", error);
        });
    },
    deactivateRoute(route, elt) {
      return this.$ankApi
        .delete(encodeURI("admin/routes/" + route + "/deactivate/"))
        .then(response => {
          if (response.status === 200 && response.statusText === "OK") {
            elt.active = false;
          } else {
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
    this.restoreTreeState();
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
