export default {
    data() {
        return {
            groupTree: new kendo.data.HierarchicalDataSource({
                transport: {
                    read: (options) => {
                        Vue.ankApi.get("admin/account/groups/").then(response => {
                            if (response.status === 200 && response.statusText === 'OK') {
                                let groups = response.data.groups;
                                Object.values(groups).forEach((currentData) => {
                                    currentData.items = currentData.items || [];
                                    currentData.parents.forEach((parentData) => {
                                        try {
                                            groups[parentData].items = groups[parentData].items || [];
                                            groups[parentData].items.push(currentData);
                                        } catch (e) {

                                        }
                                    });
                                });
                                //Suppress first level elements
                                Object.values(groups).forEach((currentData) => {
                                    if (currentData.parents.length > 0) {
                                        delete groups[currentData.accountId];
                                    }
                                });

                                try {
                                    //Suppress refs elements and keep only values
                                    groups = Object.values(JSON.parse(JSON.stringify(groups)));
                                } catch (e) {
                                    groups = [];
                                }
                                const addUniqId = (currentElement, id = "") => {
                                    currentElement.hierarchicalId = id ? id + "/" + currentElement.documentId : currentElement.documentId;
                                    if (currentElement.items) {
                                        currentElement.items.forEach((childrenElement) => {
                                            addUniqId(childrenElement, currentElement.hierarchicalId);
                                        })
                                    }
                                };
                                groups.forEach(currentGroup => {
                                    addUniqId(currentGroup);
                                });
                                const selectedElement = window.localStorage.getItem("admin.account.groupSelected");
                                const restoreExpandedTree = (data, expanded) => {
                                    for (let i = 0; i < data.length; i++) {
                                        if (expanded["#all"] ===true || expanded[data[i].hierarchicalId]) {
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
                                let expandedElements = window.localStorage.getItem("admin.account.expandedElement");
                                if (expandedElements) {
                                    try {
                                        expandedElements = JSON.parse(expandedElements);
                                        restoreExpandedTree(groups, expandedElements);
                                    } catch (e) {

                                    }
                                }
                                const toDisplay = [
                                    {
                                        "login": "@users",
                                        "documentId": "@users",
                                        "accountId": "@users",
                                        "hierarchicalId": "@users",
                                        "expanded": expandedElements && (expandedElements["#all"] || expandedElements["@users"]),
                                        "selected": "@users" === selectedElement,
                                        "parents": [],
                                        "title": "All users",
                                        "nbUser": response.data.nbUsers ? response.data.nbUsers : "??",
                                        "items": groups
                                    }
                                ];
                                options.success(toDisplay);
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
            userModeSelected: false,
            displayGroupDocument: false,
            options: {}
        };
    },
    mounted() {
        this.fetchConfig();
        this.bindTree();
        this.bindGrid();
        this.bindSplitter();
    },
    methods: {
        fetchConfig: function() {
            Vue.ankApi.get("admin/account/config/").then(response => {
                if (response.status === 200 && response.statusText === 'OK') {
                    this.options = response.data;
                    this.bindToolbars(response.data);
                } else {
                    throw new Error(response);
                }
            }).catch((error) => {
                console.error("Unable to get options", error);
            });
        },
        bindToolbars: function(element) {
            const openDoc = this.$refs.openDoc;
            const groupToolbar = this.$refs.groupToolbar.kendoWidget();
            const toggleUserMode = this.toggleUserMode.bind(this);
            const openInCreation = (event) => {
                if (event && event.target && event.target[0] && event.target[0].id) {
                    openDoc.initid = event.target[0].id;
                    openDoc.viewid = "!defaultCreation";
                    toggleUserMode();
                }
            };
            groupToolbar.add({
                type: "splitButton",
                text: "Create",
                menuButtons: element.group
            });
            groupToolbar.bind("click", openInCreation);
            const userToolbar = this.$refs.userToolbar.kendoWidget();
            userToolbar.add({
                type: "splitButton",
                text: "Create",
                menuButtons: element.user
            });
            userToolbar.bind("click", openInCreation);
        },
        //Bind the tree events
        bindTree: function () {
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
        },
        //Bind the grid events (click to open an user)
        bindGrid: function () {
            const grid = this.$refs.grid.$el;
            const openDoc = this.$refs.openDoc;
            const toggleUserMode = this.toggleUserMode.bind(this);
            Vue.jquery(grid).on("click", ".openButton", (event) => {
                event.preventDefault();
                console.log(event);
                const userId = event.currentTarget.dataset["initid"];
                if (userId) {
                    openDoc.initid = userId;
                    toggleUserMode();
                }
            });
        },
        //Create the splitter system
        bindSplitter: function () {
            const onContentResize = (part, $split) => {
                return () => {
                    window.setTimeout(() => {
                        Vue.jQuery(window).trigger("resize");
                    }, 100);
                    window.localStorage.setItem("admin.account." + part, Vue.jQuery($split).data("kendoSplitter").size(".k-pane:first"));
                }
            };
            const sizeContentPart = window.localStorage.getItem("admin.account.content") || "200px";
            const sizeCenterPart = window.localStorage.getItem("admin.account.center") || "200px";
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
        //Display the user pane
        toggleUserMode: function () {
            this.userModeSelected = !this.userModeSelected;
        },
        //Manually refresh the tree pane
        updateTreeData: function () {
            this.groupTree.read();
        },
        //Display the selected group in the ank-document
        updateGroupSelected: function (selectedGroupId) {
            const groupDoc = this.$refs.groupDoc;
            if (selectedGroupId && selectedGroupId !== "@users") {
                this.displayGroupDocument = true;
                groupDoc.initid = selectedGroupId;
                return;
            }
            this.displayGroupDocument = false;
        },
        //Refresh the grid with the new selected group
        updateGridData: function (selectedGroupLogin) {
            const grid = this.$refs.grid.kendoWidget();
            grid.clearSelection();
            if (selectedGroupLogin === "@users") {
                this.gridContent.filter({});
            } else {
                this.gridContent.filter({field: "group", operator: "equal", value: selectedGroupLogin});
            }

        },
        //Update the selected group
        onGroupSelect: function (event) {
            const selectedElement = event.sender.dataItem(event.sender.select());
            window.localStorage.setItem("admin.account.groupSelected", selectedElement.hierarchicalId);
            this.updateGroupSelected(selectedElement.documentId);
            this.updateGridData(selectedElement.login);
        },
        //Register the leaf open and closed
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
                window.localStorage.setItem("admin.account.expandedElement", JSON.stringify(expandedItemsIds));
            }).bind(this);
            window.setTimeout(saveTreeView, 100);
        },
        //Close all the leafs
        collapseAll: function () {
            window.localStorage.setItem("admin.account.expandedElement", JSON.stringify({"#all": false}));
            this.updateTreeData();
        },
        //Expand all the leafs
        expandAll: function () {
            window.localStorage.setItem("admin.account.expandedElement", JSON.stringify({"#all": true}));
            this.updateTreeData();
        },
        //Disable all the group non selected
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