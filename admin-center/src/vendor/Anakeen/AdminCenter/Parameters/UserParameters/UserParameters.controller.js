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
                        width: '6rem',
                        filterable: false,
                        template: '<button class="btn btn-secondary selection-btn">Select</button>',
                    },
                ],
                filterable: false,
                resizable: false,
                messages: {
                    noRows: 'Search a user to modify his settings',
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
                    <button class="btn btn-primary toolbar-btn back-btn">
                        <i class="material-icons">arrow_back</i>
                        <span>User search</span>
                    </button>
                    <button class="btn btn-secondary toolbar-btn refresh-btn">
                        <i class="material-icons">refresh</i>
                    </button>
                    <button class="btn btn-secondary toolbar-btn expand-btn">
                        <i class="material-icons">expand_more</i>
                    </button>
                    <button class="btn btn-secondary toolbar-btn collapse-btn">
                        <i class="material-icons">expand_less</i>
                    </button>
                    <div id="search-input" class="input-group">
                        <input type="text" class="form-control global-search-input" placeholder="Filter parameters...">
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
                        '<button class="btn btn-secondary edition-btn" title="Edit" style="margin-right: .4rem;"><i class="material-icons" style="font-size: 1.3rem;">edit</i></button>' +
                        '# if (data.forUser) { #' +
                        '<button class="btn btn-secondary delete-btn" title="Restore system value"><i class="material-icons" style="font-size: 1.3rem;">settings_backup_restore</i></button>' +
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
                .on('click', '.refresh-btn', () => this.userParametersDataSource.read())
                .on('click', '.expand-btn', () => this.expand(true))
                .on('click', '.collapse-btn', () => this.expand(false))
                .on('input', '.global-search-input', (e) => this.searchParameters(e.currentTarget.value))
                .on('click', '.reset-search-btn', () => {
                    this.$('.global-search-input').val('');
                    this.searchParameters('');
                });
        },

        selectUser(dataItem) {
            // Set new DataSource
            this.actualLogin = dataItem.login;
            this.userParametersDataSource = new kendo.data.TreeListDataSource({
                transport: {
                    read: {
                        url: '/api/v2/admin/parameters/' + this.actualLogin + '/',
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
            this.editRoute = 'admin/parameters/' + this.actualLogin + '/' + dataItem.nameSpace + '/' + dataItem.name + '/';
        },

        deleteParameter(dataItem) {
            Vue.ankApi.delete('parameters/' + this.actualLogin + '/' + dataItem.nameSpace + '/' + dataItem.name + '/')
                .then(() => {
                    //TODO Display window to confirm deletion of parameter
                });
        },

        searchUser() {
            let user = this.$('#user-search-input').val();
            let usersDataSource = new kendo.data.TreeListDataSource({
                transport: {
                    read: {
                        url: '/api/v2/admin/parameters/users/' + user,
                    },
                },
            });
            this.$('#users-tree').data('kendoTreeList').setDataSource(usersDataSource);
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
            }, 3000);
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
    },

    mounted() {
        this.initUserTreeList();
        this.initTreeList();
        this.$('#parameters-div').attr('style', 'display: none !important;');
        this.$('#user-search-input').focus();

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
