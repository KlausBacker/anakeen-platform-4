import parameterEditor from '../ParameterEditor/ParameterEditor.vue';

export default {
    name: 'admin-center-user-parameters',

    components: {
        'admin-center-parameters-editor': parameterEditor,
    },

    data() {
        return {
            userParametersDataSource: [],

            editedItem: null,
            editRoute: '',
            actualLogin: '',
            inputSearchValue: '',
        };
    },

    methods: {
        initUserTreeList() {
            this.$('#users-tree').kendoTreeList({
                columns: [
                    { field: 'login', title: 'Login' },
                    { field: 'firstname', title: 'First name' },
                    { field: 'lastname', title: 'Last name' },
                    {
                        width: '14rem',
                        filterable: false,
                        template: '<a class="selection-btn">Select user</a>',
                    },
                ],
                dataSource: {
                    transport: {
                        read: {
                            url: '/api/v2/admin/parameters/users/',
                        },
                    },
                },
                // height: '100%',
                filterable: false,
                resizable: false,
                messages: {
                    noRows: 'Search a user to modify his settings',
                },
                dataBound: () => {
                    this.$('.selection-btn').kendoButton({
                        icon: 'user',
                    });
                },
            })
                .on('click', '.selection-btn', (e) => {
                    let treeList = $(e.delegateTarget).data('kendoTreeList');
                    let dataItem = treeList.dataItem(e.currentTarget);
                    this.selectUser(dataItem);
                });
        },

        initTreeList() {
            let toolbarTemplate = `
                <div class="user-parameters-toolbar">
                    <a class="back-btn">Search another user</a>
                    <a class="refresh-btn"></a>
                    <a class="expand-btn"></a>
                    <a class="collapse-btn"></a>
                    <div id="search-input" class="input-group">
                        <input type="text"
                               class="form-control global-search-input"
                               placeholder="Filter parameters..."
                               style="border-radius: .25rem">
                        <i class="input-group-addon material-icons reset-search-btn">close</i>
                    </div>
                </div>
            `;

            let headerAttributes = { 'class': 'user-filterable-header', }; // jscs:ignore disallowQuotedKeysInObjects

            this.$('#user-parameters-tree').kendoTreeList({
                dataSource: this.userParametersDataSource,
                columns: [
                    { field: 'name', title: 'Name', headerAttributes: headerAttributes },
                    { field: 'description', title: 'Description', headerAttributes: headerAttributes },
                    { field: 'value', title: 'User value', headerAttributes: headerAttributes },
                    { field: 'initialValue', title: 'System value', headerAttributes: headerAttributes },
                    {
                        width: '10rem',
                        filterable: false,
                        template: '# if (!data.rowLevel && !data.isStatic && !data.isReadOnly) { #' +
                        '<a class="edition-btn" title="Edit"></a>' +
                        '# if (data.forUser) { #' +
                        '<a class="delete-btn" title="Restore system value"></a>' +
                        '# } #' +
                        '# } #',
                    },
                ],
                filterable: false,
                toolbar: toolbarTemplate,
                resizable: false,

                expand: (e) => {
                    this.addClassToRow(e.sender);
                    this.saveTreeState();
                },

                collapse: (e) => {
                    this.addClassToRow(e.sender);
                    this.saveTreeState();
                },

                dataBound: (e) => {
                    this.addClassToRow(e.sender);
                    this.restoreTreeState();

                    // Init kendo button in tree
                    this.$('.edition-btn').kendoButton({
                        icon: 'edit',
                    });
                    this.$('.delete-btn').kendoButton({
                        icon: 'undo',
                    });
                },
            })
                .on('click', '.edition-btn', (e) => {
                    let treeList = $(e.delegateTarget).data('kendoTreeList');
                    let dataItem = treeList.dataItem(e.currentTarget);

                    this.openEditor(dataItem);
                })
                .on('click', '.delete-btn', (e) => {
                    let treeList = $(e.delegateTarget).data('kendoTreeList');
                    let dataItem = treeList.dataItem(e.currentTarget);

                    this.deleteParameter(dataItem);
                })
                .on('click', '.back-btn', () => {
                    this.actualLogin = '';

                    // Display user search
                    this.$('#user-search').css('display', '');
                    this.$('#parameters-div').attr('style', (i, s) =>  s + 'display: none !important;');

                    // Focus on search input
                    this.$('#user-search-input').focus();
                })
                .on('click', '.refresh-btn', () => {
                    this.userParametersDataSource.read();
                })
                .on('click', '.expand-btn', () => this.expand(true))
                .on('click', '.collapse-btn', () => this.expand(false))
                .on('input', '.global-search-input', (e) => this.searchParameters(e.currentTarget.value))
                .on('click', '.reset-search-btn', () => {
                    this.$('.global-search-input').val('');
                    this.searchParameters('');
                });

            // Init kendo button of toolbar
            this.$('.back-btn').kendoButton({
                icon: 'arrow-chevron-left',
            });
            this.$('.refresh-btn').kendoButton({
                icon: 'reload',
            });
            this.$('.expand-btn').kendoButton({
                icon: 'arrow-60-down',
            });
            this.$('.collapse-btn'). kendoButton({
                icon: 'arrow-60-up',
            });
        },

        selectUser(dataItem) {
            // Set new DataSource
            this.actualLogin = dataItem.login;
            this.userParametersDataSource = new kendo.data.TreeListDataSource({
                transport: {
                    read: {
                        url: '/api/v2/admin/parameters/users/' + this.actualLogin + '/',
                    },
                },
            });
            this.$('#user-parameters-tree').data('kendoTreeList').setDataSource(this.userParametersDataSource);

            // Display parameters and hide user search
            this.$('#user-search').css('display', 'none');
            this.$('#parameters-div').css('display', '');

            // Focus on filter input
            this.$('.global-search-input').focus();
        },

        openEditor(dataItem) {
            this.editedItem = dataItem;
            this.editRoute = 'admin/parameters/'
                             + this.actualLogin + '/'
                             + dataItem.nameSpace + '/'
                             + dataItem.name + '/';
        },

        deleteParameter(dataItem) {
            Vue.ankApi.delete('admin/parameters/' + this.actualLogin + '/' + dataItem.nameSpace + '/' + dataItem.name + '/')
                .then(() => {
                    this.$('.delete-confirmation-window').kendoWindow({
                        modal: true,
                        draggable: false,
                        resizable: false,
                        title: 'Parameter restored',
                        width: '30%',
                        visible: false,
                        actions: [],
                    }).data('kendoWindow').center().open();
                });
            this.userParametersDataSource.read();
        },

        closeDeleteConfirmation() {
            this.$('.delete-confirmation-window').data('kendoWindow').close();
        },

        searchUser() {
            let user = this.$('#user-search-input').val();
            if (user.trim()) {
                let usersDataSource = new kendo.data.TreeListDataSource({
                    transport: {
                        read: {
                            url: '/api/v2/admin/parameters/users/search/' + user + '/',
                        },
                    },
                });
                this.$('#users-tree').data('kendoTreeList').setDataSource(usersDataSource);
            }
        },

        searchParameters(researchTerms) {
            if (researchTerms) {
                this.userParametersDataSource.filter({
                    logic: 'or',
                    filters: [
                        { field: 'name', operator: 'contains', value: researchTerms },
                        { field: 'description', operator: 'contains', value: researchTerms },
                        { field: 'value', operator: 'contains', value: researchTerms },
                        { field: 'initialValue', operator: 'contains', value: researchTerms },
                    ],
                });

                // Add icon to show filter effect to the user
                if (!this.$('.user-filterable-header').children('.filter-icon').length) {
                    this.$('.user-filterable-header')
                        .append(this.$('<i class="material-icons filter-icon">filter_list</i>'));
                }

                this.expand(true);
            } else {
                this.userParametersDataSource.filter({});

                // Remove filter icon when nothing is filtered
                this.$('.user-filterable-header').children('.filter-icon').remove();
            }
        },

        addClassToRow(treeList) {
            let items = treeList.items();

            // setTimeout(function, 0) to add CSS classes when all DOM content has been updated
            setTimeout(() => {
                items.each(function addTypeClass() {
                    let dataItem = treeList.dataItem(this);
                    if (dataItem.rowLevel) {
                        $(this).addClass('grid-expandable');
                        $(this).addClass('grid-level-' + dataItem.rowLevel);
                    }
                });
            }, 0);
        },

        expand(expansion) {
            let treeList = this.$('#user-parameters-tree').data('kendoTreeList');
            let $rows = this.$('tr.k-treelist-group', treeList.tbody);
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

        updateAtEditorClose() {
            setTimeout(() => {
                this.editedItem = null;
                this.editRoute = '';
            }, 300);
            this.userParametersDataSource.read();
        },

        saveTreeState() {
            // setTimeout(function, 0) to add CSS classes when all DOM content has been updated
            setTimeout(() => {
                let treeState = [];
                let treeList = this.$('#user-parameters-tree').data('kendoTreeList');
                let items = treeList.items();
                items.each((index, item) => {
                    if ($(item).attr('aria-expanded') === 'true') {
                        treeState.push(index);
                    }
                });
                window.localStorage.setItem('admin.user-parameters.treeState', JSON.stringify(treeState));
            }, 0);
        },

        restoreTreeState() {
            let treeState = window.localStorage.getItem('admin.user-parameters.treeState');
            if (treeState) {
                let treeList = this.$('#user-parameters-tree').data('kendoTreeList');
                let $rows = this.$('tr', treeList.tbody);
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

        clearSearchInput() {
            this.$('#user-search-input').val('');
        },

        switchParameters() {
            let editor = this.$('.edition-window').data('kendoWindow');
            if (editor) {
                editor.destroy();
            }

            this.$emit('switchParameters');
        },
    },

    computed: {
        isSearchButtonDisabled() {
            return (this.inputSearchValue === '');
        },
    },

    mounted() {
        this.initUserTreeList();
        this.initTreeList();

        // Init switch button
        this.$('.switch-parameters').kendoButton({
            icon: 'arrow-left',
        });

        // Hide user parameters tree to show user search
        this.$('#parameters-div').attr('style', 'display: none !important;');

        // Focus on input for quick search
        this.$('#user-search-input').focus();

        // Add event listener on treeList to expand/collapse rows on click
        this.$('#user-parameters-tree').on('mouseup', 'tbody > .grid-expandable', (e) => {
            let treeList = this.$(e.delegateTarget).data('kendoTreeList');
            if ($(e.currentTarget).attr('aria-expanded') === 'false') {
                treeList.expand(e.currentTarget);
            } else {
                treeList.collapse(e.currentTarget);
            }

            this.saveTreeState();
        });

        // At window resize, resize the treeList
        window.addEventListener('resize', () => {
            let $tree = this.$('#user-parameters-tree');
            let kTree = $tree.data('kendoTreeList');
            if (kTree) {
                $tree.height(this.$(window).height() - $tree.offset().top);
                kTree.resize();
            }

            let $userTree = this.$('#users-tree');
            let kUserTree = $userTree.data('kendoTreeList');
            if (kUserTree) {
                $userTree.height(this.$(window).height() - $userTree.offset().top);
                kUserTree.resize();
            }
        });

    },
};
