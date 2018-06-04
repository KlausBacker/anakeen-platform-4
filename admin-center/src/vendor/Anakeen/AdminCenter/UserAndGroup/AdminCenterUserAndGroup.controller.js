export default {
    data() {
        return {
            groupTree: new kendo.data.HierarchicalDataSource({
                transport: {
                    read: (options) => {
                        Vue.ankApi.get("admin/account/groups/").then(response => {
                            if (response.status === 200 && response.statusText === 'OK') {
                                let data = response.data;
                                Object.values(data).forEach((currentData) => {
                                    currentData.items = currentData.items || [];
                                    currentData.parents.forEach((parentData) => {
                                        try {
                                            data[parentData].items = data[parentData].items || [];
                                            data[parentData].items.push(currentData);
                                        } catch (e) {

                                        }
                                    });
                                });
                                //Suppress first level elements
                                Object.values(data).forEach((currentData) => {
                                    if (currentData.parents.length > 0) {
                                        delete data[currentData.accountId];
                                    }
                                });

                                try {
                                    //Suppress refs elements and keep only values
                                    data = Object.values(JSON.parse(JSON.stringify(data)));
                                } catch (e) {
                                    data = [];
                                }
                                const addUniqId = (currentElement, id = "") => {
                                    currentElement.hierarchicalId = id ? id + "/" + currentElement.documentId : currentElement.documentId;
                                    if (currentElement.items) {
                                        currentElement.items.forEach((childrenElement) => {
                                            addUniqId(childrenElement, currentElement.hierarchicalId);
                                        })
                                    }
                                };
                                data.forEach(currentGroup => {
                                    addUniqId(currentGroup);
                                });
                                const selectedElement = window.localStorage.getItem("admin.userAndGroup.groupSelected");
                                const restoreExpandedTree = (data, expanded) => {
                                    for (let i = 0; i < data.length; i++) {
                                        if (expanded[data[i].hierarchicalId]) {
                                            data[i].expanded = true;
                                        }
                                        if (data[i].hierarchicalId === selectedElement) {
                                            data[i].selected = true;
                                        }
                                        if (data[i].items && data[i].items.length) {
                                            restoreExpandedTree(data[i].items, expanded);
                                        }
                                    }
                                };
                                let expandedElements = window.localStorage.getItem("admin.userAndGroup.expandedElement");
                                if (expandedElements) {
                                    try {
                                        restoreExpandedTree(data, JSON.parse(expandedElements));
                                    } catch (e) {

                                    }
                                }
                                options.success(data);
                            } else {
                                throw new Error("Unable to get groups");
                            }
                        }).catch((error) => {
                            console.error("Unable to get group", error);
                        })
                    }
                },
                schema: {
                    model: {
                        id: "hierarchicalId",
                        children: "items"
                    }
                }
            }),
            gridContent: new kendo.data.DataSource({
                transport: {
                    read: {
                        url: '/api/v2/admin/account/users/'
                    }
                },
                schema: {
                    data: "data",
                    total: "total",
                    model: {
                        id: "id",
                        fields: {
                            login: {type: "string"},
                            firstname: {type: "string"},
                            lastname: {type: "string"},
                            mail: {type: "string"},
                        }
                    }
                },
                serverFiltering: true,
                serverPaging: true,
                serverSorting: true,
                pageSize: 10
            }),
            userModeSelected: false
        };
    },
    mounted() {
        this.bindTree();
        this.bindGrid();
        this.bindSplitter();
    },
    methods: {
        updateTreeData: function () {
            this.groupTree.read();
        },
        bindTree: function () {
            const treeview = this.$refs.groupTreeView.kendoWidget();
            treeview.bind("dataBound", () => {
                const selectedElement = treeview.dataItem(treeview.select());
                if (selectedElement.documentId) {
                    this.updateGroupSelected(selectedElement.documentId);
                }
                this.updateGridData(selectedElement.login);
            });
        },
        bindGrid: function () {
            const grid = this.$refs.grid.$el;
            const userDocument = this.$refs.openUser;
            const toggleUserMode = this.toggleUserMode.bind(this);
            Vue.jquery(grid).on("click", ".openButton", (event) => {
                event.preventDefault();
                console.log(event);
                const userId = event.currentTarget.dataset["initid"];
                if (userId) {
                    userDocument.initid = userId;
                    toggleUserMode();
                }
            });
        },
        bindSplitter: function () {
            const onContentResize = (part, $split) => {
                return () => {
                    window.setTimeout(() => {
                        Vue.jQuery(window).trigger("resize");
                    }, 100);
                    window.localStorage.setItem("admin.userAndGroup." + part, Vue.jQuery($split).data("kendoSplitter").size(".k-pane:first"));
                }
            };
            const sizeContentPart = window.localStorage.getItem("admin.userAndGroup.content") || "200px";
            const sizeCenterPart = window.localStorage.getItem("admin.userAndGroup.center") || "200px";
            Vue.jQuery(this.$refs.gridAndTreePart).kendoSplitter({
                panes: [
                    {collapsible: true, size: sizeContentPart, min: "200px", resizable: true},
                    {collapsible: false, resizable: true}
                ],
                resize: onContentResize("content", this.$refs.gridAndTreePart)
            });
            Vue.jQuery(this.$refs.centerPart).kendoSplitter({
                orientation: "vertical",
                panes: [
                    {collapsible: true, size: sizeCenterPart, min: "200px", resizable: true},
                    {collapsible: false, resizable: true}
                ],
                resize: onContentResize("center", this.$refs.centerPart)
            })
        },
        toggleUserMode: function () {
            this.userModeSelected = !this.userModeSelected;
        },
        updateGroupSelected: function (selectedGroupId) {
            const groupDoc = this.$refs.groupDoc;
            groupDoc.initid = selectedGroupId;
        },
        updateGridData: function (selectedGroupLogin) {
            const grid = this.$refs.grid.kendoWidget();
            grid.clearSelection();
            this.gridContent.filter({field: "group", operator: "equal", value: selectedGroupLogin});
        },
        onGroupSelect: function (event) {
            const selectedElement = event.sender.dataItem(event.sender.select());
            window.localStorage.setItem("admin.userAndGroup.groupSelected", selectedElement.hierarchicalId);
            this.updateGroupSelected(selectedElement.documentId);
            this.updateGridData(selectedElement.login);
        },
        registerTreeState: function (event) {
            const saveTreeView = (function () {
                const treeview = this.$refs.groupTreeView.kendoWidget();
                const expandedItemsIds = {};
                treeview.element.find(".k-item").each(function () {
                    let item = treeview.dataItem(this);
                    if (item.expanded) {
                        expandedItemsIds[item.hierarchicalId] = true;
                    }
                });
                window.localStorage.setItem("admin.userAndGroup.expandedElement", JSON.stringify(expandedItemsIds));
            }).bind(this);
            window.setTimeout(saveTreeView, 100);
        },
        collapseAll: function () {
            const treeview = this.$refs.groupTreeView.kendoWidget();
            treeview.collapse(".k-item");
        },
        expandAll: function () {
            const treeview = this.$refs.groupTreeView.kendoWidget();
            treeview.expand(".k-item");
        },
        filterGroup: function (event) {
            event.preventDefault();
            const filter = (dataSource, query) => {
                let hasVisibleChildren = false;
                const data = dataSource instanceof kendo.data.HierarchicalDataSource && dataSource.data();
                for (let i = 0; i < data.length; i++) {
                    let item = data[i];
                    let text = item.title.toLowerCase();
                    let itemVisible =
                        query === true // parent already matches
                        || query === "" // query is empty
                        || text.indexOf(query) >= 0; // item text matches query

                    let anyVisibleChildren = filter(item.children, itemVisible || query); // pass true if parent matches

                    hasVisibleChildren = hasVisibleChildren || anyVisibleChildren || itemVisible;

                    item.hidden = !itemVisible && !anyVisibleChildren;
                }

                if (data) {
                    // re-apply filter on children
                    dataSource.filter({field: "hidden", operator: "neq", value: true});
                }

                return hasVisibleChildren;
            };
            const query = this.$refs.filterTree.value ? this.$refs.filterTree.value.toLowerCase() : "";
            return filter(this.groupTree, query);
        }
    }
};