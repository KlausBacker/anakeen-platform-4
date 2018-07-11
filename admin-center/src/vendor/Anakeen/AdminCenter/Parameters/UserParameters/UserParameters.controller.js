import parameterEditor from '../ParameterEditor/ParameterEditor.vue';

export default {
    name: 'admin-center-user-parameters',

    components: {
        'admin-center-parameters-editor': parameterEditor,
    },

    data() {
        return {
            // Data source for user parameters treeList
            userParametersDataSource: [],

            // Current edited item and route url to modify it
            editedItem: null,
            editRoute: '',

            // Login of the selected user
            actualLogin: '',

            // Value entered in the search input
            inputSearchValue: '',
        };
    },

    methods: {
        // Init the treeList containing users (1 level treeList)
        initUserTreeList() {
            this.$('#users-tree').kendoTreeList({
                columns: [
                    { field: 'login', title: 'Login' },
                    { field: 'firstname', title: 'First name' },
                    { field: 'lastname', title: 'Last name' },
                    {
                        width: '14rem',
                        filterable: false,

                        // Button to select the user and access his parameters
                        template: '<a class="selection-btn">Select user</a>',
                    },
                ],

                // Datasource set to display the first 5 users in treeList
                dataSource: {
                    transport: {
                        read: {
                            url: '/api/v2/admin/parameters/users/',
                        },
                    },
                },

                // Disable columns filters to add global filter
                filterable: false,
                resizable: false,
                messages: {
                    noRows: 'Search a user to modify his settings',
                },
                dataBound: () => {
                    // Init kendoButtons when users are loaded from the server
                    this.$('.selection-btn').kendoButton({
                        icon: 'user',
                    });
                },
            })
                .on('click', '.selection-btn', (e) => {
                    // Select a user to display his parameters with the data item
                    let treeList = this.$(e.delegateTarget).data('kendoTreeList');
                    let dataItem = treeList.dataItem(e.currentTarget);
                    this.selectUser(dataItem);
                });
        },

        // Init the treeList containing all the parameters for the selected user
        initTreeList() {
            // Custom toolbar template to add a global filter
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
                        <i class="input-group-addon material-icons reset-search-btn parameter-search-reset-btn">close</i>
                    </div>
                </div>
            `;

            // Add a class on filterable columns header to diplay a filter icon when filtering
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

                        // Display edition button on modifiable parameters
                        // and restore/delete button on user defined parameters
                        template: '# if (!data.rowLevel && !data.isStatic && !data.isReadOnly) { #' +
                        '<a class="edition-btn" title="Edit"></a>' +
                        '# if (data.forUser) { #' +
                        '<a class="delete-btn" title="Restore system value"></a>' +
                        '# } #' +
                        '# } #',
                    },
                ],

                // Disable filter on columns to add a global filter
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

                    // Init kendoButtons in tree
                    this.$('.edition-btn').kendoButton({
                        icon: 'edit',
                    });
                    this.$('.delete-btn').kendoButton({
                        icon: 'undo',
                    });
                },
            })
                .on('click', '.edition-btn', (e) => {
                    // Open parameter editor with selected dataItem
                    let treeList = this.$(e.delegateTarget).data('kendoTreeList');
                    let dataItem = treeList.dataItem(e.currentTarget);
                    this.openEditor(dataItem);
                })
                .on('click', '.delete-btn', (e) => {
                    // Delete/Restore user parameter with selected dataItem
                    let treeList = this.$(e.delegateTarget).data('kendoTreeList');
                    let dataItem = treeList.dataItem(e.currentTarget);
                    this.deleteParameter(dataItem);
                })
                .on('click', '.back-btn', () => {
                    // Reset actual login when returning to user selection
                    this.actualLogin = '';

                    // Display user search
                    this.$('#user-search').css('display', '');
                    this.$('#parameters-div').attr('style', (i, s) =>  s + 'display: none !important;');

                    // Focus on search input
                    this.$('#user-search-input').focus();

                    // Resize user treeList when it is displayed
                    let $userTree = this.$('#users-tree');
                    let kUserTree = $userTree.data('kendoTreeList');
                    if (kUserTree) {
                        $userTree.height(this.$(window).height() - $userTree.offset().top);
                        kUserTree.resize();
                    }
                })
                .on('click', '.refresh-btn', () => {
                    // Re-fetch data from server
                    this.userParametersDataSource.read();
                })
                .on('click', '.expand-btn', () => this.expand(true))
                .on('click', '.collapse-btn', () => this.expand(false))
                .on('input', '.global-search-input', (e) => this.searchParameters(e.currentTarget.value))
                .on('click', '.reset-search-btn', () => {
                    this.$('.global-search-input').val('');
                    this.searchParameters('');
                });

            // Init kendoButtons of toolbar
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

        // Select a user in user treeList and display his parameters
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

            // Resize user parameters treeList when displaying it
            let $tree = this.$('#user-parameters-tree');
            let kTree = $tree.data('kendoTreeList');
            if (kTree) {
                $tree.height(this.$(window).height() - $tree.offset().top);
                kTree.resize();
            }

            // Focus on filter input
            this.$('.global-search-input').focus();
        },

        // Open the parameter editor with the correct dataItem and modification route url
        openEditor(dataItem) {
            this.editedItem = dataItem;
            this.editRoute = 'admin/parameters/'
                             + this.actualLogin + '/'
                             + dataItem.nameSpace + '/'
                             + dataItem.name + '/';
        },

        // Send a request to the server to remove the user definition of the passed parameter
        // To restore the system value of this parameter for the user
        deleteParameter(dataItem) {
            this.$ankApi.delete('admin/parameters/'
                + this.actualLogin + '/'
                + dataItem.nameSpace + '/'
                + dataItem.name + '/')
                .then(() => {
                    // Show a confirmation window to notify the user of the modification
                    this.$('.delete-confirmation-window').kendoWindow({
                        modal: true,
                        draggable: false,
                        resizable: false,
                        title: 'Parameter restored',
                        width: '30%',
                        visible: false,
                        actions: [],
                    }).data('kendoWindow').center().open();

                    // Init the confirmation window's close kendoButton
                    this.$('.delete-confirmation-btn').kendoButton({
                        icon: 'arrow-chevron-left',
                    });

                    // Re-fetch the parameters from server to display the updated values
                    this.userParametersDataSource.read();
                })
                .catch(() => {
                    // Show an error window to nofity the user that the parameter restoration failed
                    this.$('.delete-error-window').kendoWindow({
                        modal: true,
                        draggable: false,
                        resizable: false,
                        title: 'Error',
                        width: '30%',
                        visible: false,
                        actions: [],
                    }).data('kendoWindow').center().open();

                    // Init error window's close kendoButton
                    this.$('.delete-error-btn').kendoButton({
                        icon: 'arrow-chevron-left',
                    });
                });
        },

        // Close restoration confirmation window
        closeDeleteConfirmation() {
            this.$('.delete-confirmation-window').data('kendoWindow').close();
        },

        // Close restoration error window
        closeDeleteError() {
            this.$('.delete-error-window').data('kendoWindow').close();
        },

        // Search a user on the server in users treeList
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

        // Filter treeList parameters on name, description, value and initial system value
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

                // Expand treeList to display all results
                this.expand(true);
            } else {
                // Reset filter passing an empty one
                this.userParametersDataSource.filter({});

                // Remove filter icon when nothing is filtered
                this.$('.user-filterable-header').children('.filter-icon').remove();
            }
        },

        // Add a class to level 1 and 2 rows of treeList, to add custom CSS
        addClassToRow(treeList) {
            let items = treeList.items();
            let _this = this;

            // setTimeout(function, 0) to add CSS classes when all DOM content has been updated
            setTimeout(() => {
                items.each(function addTypeClass() {
                    let dataItem = treeList.dataItem(this);
                    if (dataItem.rowLevel) {
                        _this.$(this).addClass('grid-expandable grid-level-' + dataItem.rowLevel);
                    }
                });
            }, 0);
        },

        // Expand/Collapse every rows of the user parameters tree list (true => expand / false => collapse)
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

        // At editor close, update the value in treeList, and reset editedItem and editionRoute
        updateAtEditorClose(newValue) {
            setTimeout(() => {
                if (newValue) {
                    this.editedItem.set('value', newValue);
                }

                this.editedItem = null;
                this.editRoute = '';
            }, 300);
            this.userParametersDataSource.read();
        },

        // Save the current user parameters tree state to localStorage
        saveTreeState() {
            // setTimeout(function, 0) to add CSS classes when all DOM content has been updated
            setTimeout(() => {
                let treeState = [];
                let treeList = this.$('#user-parameters-tree').data('kendoTreeList');
                let items = treeList.items();
                items.each((index, item) => {
                    if (this.$(item).attr('aria-expanded') === 'true') {
                        treeState.push(index);
                    }
                });
                window.localStorage.setItem('admin.user-parameters.treeState', JSON.stringify(treeState));
            }, 0);
        },

        // Restore the user parameters tree state from localStorage, if it exists
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

        // Empty the value of the search input
        clearSearchInput() {
            this.$('#user-search-input').val('');
        },

        // Destroy the parameter editor if it exists and emit event to display System parameters
        switchParameters() {
            let editor = this.$('.edition-window').data('kendoWindow');
            if (editor) {
                editor.destroy();
            }

            this.$emit('switchParameters');
        },
    },

    computed: {
        // Used in template to enable/disable the search input
        isSearchButtonDisabled() {
            return (this.inputSearchValue === '');
        },
    },

    mounted() {
        // Init treeList to display users
        this.initUserTreeList();

        // Init treeList to display user parameters
        this.initTreeList();

        // Init switch button
        this.$('.switch-parameters').kendoButton({
            icon: 'arrow-left',
        });

        // Hide user parameters tree to show user search
        this.$('#parameters-div').attr('style', 'display: none !important;');

        // Focus on input for quick search
        this.$('#user-search-input').focus();

        // Add event listener on treeList to expand/collapse rows on clic
        // And remove mousedown event listener to prevent double expand/collapse at click on arrows pf treeList
        this.$('#user-parameters-tree')
            .off('mousedown')
            .on('mouseup', 'tbody > .grid-expandable', (e) => {
            let treeList = this.$(e.delegateTarget).data('kendoTreeList');
            if (this.$(e.currentTarget).attr('aria-expanded') === 'false') {
                treeList.expand(e.currentTarget);
            } else {
                treeList.collapse(e.currentTarget);
            }

            this.addClassToRow(treeList);
            this.saveTreeState();
        });

        // At window resize, resize the treeLists
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
