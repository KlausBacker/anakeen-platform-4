export default {
    data() {
        return {
            groupTree: new kendo.data.HierarchicalDataSource({
                data: []
            }),
            gridContent: new kendo.data.DataSource({
                data: []
            })
        };
    },
    mounted () {
        this.updateData();
        this.bindSplitter();
    },
    methods : {
        bindSplitter: function() {
            const onContentResize = (part, $split) => {
                return () => {
                    window.localStorage.setItem("admin.userAndGroup."+part, Vue.jQuery($split).data("kendoSplitter").size(".k-pane:first"));
                }
            };
            const sizeContentPart = window.localStorage.getItem("admin.userAndGroup.content") || "200px";
            const sizeCenterPart = window.localStorage.getItem("admin.userAndGroup.center") || "200px";
            Vue.jQuery(this.$refs.contentPart).kendoSplitter({
                panes: [
                    { collapsible: true, size: sizeContentPart, min: "200px", resizable: true },
                    { collapsible: false, resizable: true }
                ],
                resize: onContentResize("content", this.$refs.contentPart)
            });
            Vue.jQuery(this.$refs.centerPart).kendoSplitter({
                orientation: "vertical",
                panes : [
                    { collapsible: true, size: sizeCenterPart, min: "200px", resizable: true },
                    { collapsible: false, resizable: true }
                ],
                resize: onContentResize("center", this.$refs.centerPart)
            })
        },
        updateData : function() {
            Vue.ankApi.get("admin/account/groups/").then(response => {
                if (response.status === 200 && response.statusText === 'OK') {
                    let data = response.data;
                    Object.values(data).forEach((currentData) => {
                        currentData.items = currentData.items || [];
                        currentData.parents.forEach((parentData) => {
                            try {
                                data[parentData].items = data[parentData].items || [];
                                data[parentData].items.push(currentData);
                            } catch(e) {

                            }
                        });
                    });
                    //Suppress first level elements
                    Object.values(data).forEach((currentData) => {
                        if (currentData.parents.length > 0) {
                            delete data[currentData.accountId];
                        }
                    });
                    data = Object.values(data);
                    let expandedElements = window.localStorage.getItem("admin.userAndGroup.expandedElement");
                    if (expandedElements) {
                        try {
                            this.restoreExpandedTree(data, JSON.parse(expandedElements));
                        } catch(e) {

                        }
                    }
                    this.groupTree.data(data);
                    this.restoreSelectedTreeElement();
                } else {
                    throw new Error("Unable to get groups");
                }
            }).catch((error) => {
                console.error("Unable to get group", error);
            })
        },
        restoreSelectedTreeElement: function() {
            const treeview = this.$refs.groupTreeView.kendoWidget();
            const selectedElement = window.localStorage.getItem("admin.userAndGroup.groupSelected");
            if (selectedElement) {
                const getitem = treeview.dataSource.get(selectedElement);
                const selectitem = treeview.findByUid(getitem.uid);
                treeview.select(selectitem);
            }
        },
        restoreExpandedTree: function(data, expanded) {
            for (let i = 0; i < data.length; i++) {
                if (expanded[data[i].id]) {
                    data[i].expanded = true;
                }
                if (data[i].items && data[i].items.length) {
                    this.restoreExpandedTree(data[i].items, expanded);
                }
            }
        },
        updateGridData: function(selectedGroupLogin) {
            Vue.ankApi.get("admin/account/users/?group="+selectedGroupLogin).then(response => {
                if (response.status === 200 && response.statusText === 'OK') {
                    let data = response.data;
                    try {
                        data = Object.values(data);
                    } catch(e) {
                        data = [];
                    }
                    this.gridContent.data(data);
                } else {
                    throw new Error("Unable to get groups");
                }
            }).catch((error) => {
                console.error("Unable to get group", error);
            })
        },
        onGroupSelect: function(event) {
            const selectedElement = event.sender.dataItem(event.sender.select());
            window.localStorage.setItem("admin.userAndGroup.groupSelected", selectedElement.id);
            this.updateGridData(selectedElement.login);
        },
        registerTreeState: function(event) {
            const saveTreeView = (function() {
                const treeview = this.$refs.groupTreeView.kendoWidget();
                const expandedItemsIds = {};
                treeview.element.find(".k-item").each(function () {
                    let item = treeview.dataItem(this);
                    if (item.expanded) {
                        expandedItemsIds[item.id] = true;
                    }
                });
                window.localStorage.setItem("admin.userAndGroup.expandedElement", JSON.stringify(expandedItemsIds));
            }).bind(this);
            window.setTimeout(saveTreeView, 100);
        },
        collapseAll: function() {
            const treeview = this.$refs.groupTreeView.kendoWidget();
            treeview.collapse(".k-item");
        },
        expandAll: function() {
            const treeview = this.$refs.groupTreeView.kendoWidget();
            treeview.expand(".k-item");
        }
    }
};