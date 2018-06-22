export default {
    name: 'admin-center-parameters',

    data() {
        return {
            allParametersDataSource: new kendo.data.TreeListDataSource({
                transport: {
                    read: {
                        url: '/api/v2/admin/parameters/',
                    },
                },
            }),

            usersParameters: [],

            researchTerms: '',
        };
    },

    methods: {
        initTreeList() {
            this.$('#parameters-tree').kendoTreeList({
                dataSource: this.allParametersDataSource,
                columns: [
                    { field: 'name', title: 'Name', width: '40%', },
                    { field: 'description', title: 'Description', width: '50%' },
                    { field: 'value', title: 'Value', width: '10%' },
                    {
                        title: 'Edition',
                        width: '8%',
                        filterable: false,
                        template: '# if (!data.rowLevel && !data.isStatic && !data.isReadOnly) { #' +
                        '<button class="btn">Edit</button>' +
                        '# } #',
                    },
                ],
                resizable: true,
                filterable: {
                    extra: false,
                    messages: {
                        and: 'and',
                        or: 'or',
                        filter: 'Apply filter',
                        clear: 'Clear filter',
                        info: 'Filter by: ',
                        isFalse: 'False',
                        isTrue: 'True',
                        selectValue: 'Select category',
                        cancel: 'Reject',
                        operator: 'Choose operator',
                        value: 'Choose value',
                    },
                    operators: {
                        string: {
                            eq: 'Equal to',
                            neq: 'Not equal to',
                            startswith: 'Starts',
                            endswith: 'Ends',
                            contains: 'Contains',
                            doesnotcontain: "Doesn't contain",
                        },
                        number: {
                            eq: 'Equal to',
                            neq: 'Not equal to',
                            gt: 'Greater than',
                            gte: 'Greater than or equal to',
                            lt: 'Less than',
                            lte: 'Less than or equal to',
                        },
                    },
                },

                expand: (e) => {
                    this.addClassToRow(e.sender);
                },

                collapse: (e) => {
                    this.addClassToRow(e.sender);
                },

                dataBound: (e) => {
                    this.addClassToRow(e.sender);
                },
            })
                .on('click', '.btn', (e) => {
                    let treeList = $(e.delegateTarget).data('kendoTreeList');
                    let dataItem = treeList.dataItem(e.currentTarget);

                    this.openEditor(dataItem);
                });
        },

        initToolbar() {
            this.$('#tree-toolbar').kendoToolBar({
                items: [
                    {
                        type: 'button',
                        icon: 'refresh',
                        click: () => {
                            this.allParametersDataSource.read();
                        },
                    },
                    {
                        type: 'button',
                        icon: 'sort-desc-sm',
                        click: () => {
                            let treeList = $('#parameters-tree').data('kendoTreeList');
                            let $rows = $('tr.k-treelist-group', treeList.tbody);
                            $.each($rows, (idx, row) => {
                                treeList.expand(row);
                            });
                            this.addClassToRow()
                        },
                    },
                    {
                        type: 'button',
                        icon: 'sort-asc-sm',
                        click: () => {
                            let treeList = $('#parameters-tree').data('kendoTreeList');
                            let $rows = $('tr.k-treelist-group', treeList.tbody);
                            $.each($rows, (idx, row) => {
                                treeList.collapse(row);
                            });
                        },
                    },
                ],
            });
        },

        openEditor(dataItem) {
            // TODO Open an editor to alter the parameter
            console.log('Open data editor');
        },

        searchParameters() {
            // TODO
            console.log('Search');
        },

        searchUsersParameters() {
            // TODO
            console.log('Search users');
        },

        addClassToRow(treeList) {
            let items = treeList.items();

            // setTimeout(function, 0) to add CSS classes when all DOM content has been updated
            setTimeout(() => {
                items.each(function addTypeClass() {
                    let dataItem = treeList.dataItem(this);
                    if (dataItem.rowLevel) {
                        $(this).addClass('grid-level-' + dataItem.rowLevel);
                    }
                });
            }, 0);
        },
    },

    mounted() {
        this.initTreeList();
        this.initToolbar();

    },
};
