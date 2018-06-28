import parameterEditor from './ParameterEditor/ParameterEditor.vue';

export default {
    name: 'admin-center-parameters',

    components: {
        'ank-parameter-editor': parameterEditor,
    },

    data() {
        return {
            allParametersDataSource: new kendo.data.TreeListDataSource({
                transport: {
                    read: {
                        url: '/api/v2/admin/parameters/',
                    },
                },
            }),

            editedItem: null,

            treeState: [],
        };
    },

    methods: {
        initTreeList() {
            let toolbarTemplate = `
                <div class="global-parameters-toolbar">
                    <button class="btn btn-secondary toolbar-btn refresh-btn"><i class="material-icons">refresh</i></button>
                    <button class="btn btn-secondary toolbar-btn expand-btn"><i class="material-icons">expand_more</i></button>
                    <button class="btn btn-secondary toolbar-btn collapse-btn"><i class="material-icons">expand_less</i></button>
                    <div id="search-input" class="input-group">
                        <input type="text" class="form-control global-search-input" placeholder="Filter parameters...">
                        <i class="input-group-addon material-icons reset-search-btn">
                            close
                        </i>
                    </div>
                </div>
            `;

            let headerAttributes = { 'class': 'filterable-header', };// jscs:ignore disallowQuotedKeysInObjects

            this.$('#parameters-tree').kendoTreeList({
                dataSource: this.allParametersDataSource,
                columns: [
                    { field: 'name', title: 'Name', headerAttributes: headerAttributes },
                    { field: 'description', title: 'Description', headerAttributes: headerAttributes },
                    { field: 'value', title: 'Value', headerAttributes: headerAttributes, },
                    {
                        width: '6rem',
                        filterable: false,
                        template: '# if (!data.rowLevel && !data.isStatic && !data.isReadOnly) { #' +
                        '<button class="btn btn-secondary edition-btn">Edit</button>' +
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
                .on('click', '.refresh-btn', () => this.allParametersDataSource.read())
                .on('click', '.expand-btn', () => this.expand(true))
                .on('click', '.collapse-btn', () => this.expand(false))
                .on('input', '.global-search-input', (e) => this.searchParameters(e.currentTarget.value))
                .on('click', '.reset-search-btn', () => {
                    this.$('.global-search-input').val('');
                    this.searchParameters('');
                });
        },

        openEditor(dataItem) {
            this.editedItem = dataItem;
        },

        searchParameters(researchTerms) {
            if (researchTerms) {
                this.allParametersDataSource.filter({
                    logic: 'or',
                    filters: [
                        { field: 'name', operator: 'contains', value: researchTerms },
                        { field: 'description', operator: 'contains', value: researchTerms },
                        { field: 'value', operator: 'contains', value: researchTerms },
                    ],
                });

                // Add icon to show filter effect to the user
                if (!this.$('.filterable-header').children('.filter-icon').length) {
                    this.$('.filterable-header')
                        .append(this.$('<i class="material-icons filter-icon">filter_list</i>'));
                }
            } else {
                this.allParametersDataSource.filter({});

                // Remove filter icon when nothing is filtered
                this.$('.filterable-header').children('.filter-icon').remove();
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
            let treeList = this.$('#parameters-tree').data('kendoTreeList');
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
            setTimeout(() => { this.editedItem = null; }, 300);
            this.allParametersDataSource.read();
        },

        saveTreeState() {
            // setTimeout(function, 0) to add CSS classes when all DOM content has been updated
            setTimeout(() => {
                this.treeState = [];
                let treeList = this.$('#parameters-tree').data('kendoTreeList');
                let items = treeList.items();
                items.each((index, item) => {
                    if ($(item).attr('aria-expanded') === 'true') {
                        this.treeState.push(index);
                    }
                });
            }, 0);
        },

        restoreTreeState() {
            let treeList = this.$('#parameters-tree').data('kendoTreeList');
            let $rows = this.$('tr', treeList.tbody);
            this.$.each($rows, (idx, row) => {
                if (this.treeState.includes(idx)) {
                    treeList.expand(row);
                } else {
                    treeList.collapse(row);
                }
            });
            this.addClassToRow(treeList);
        },
    },

    mounted() {
        this.initTreeList();

        // At window resize, resize the tree list to fit the window
        window.addEventListener('resize', () => {
            let $tree = this.$('#parameters-tree');
            let kTree = $tree.data('kendoTreeList');
            if (kTree) {
                $tree.height(this.$(window).height() - $tree.offset().top - 4);
                kTree.resize();
            }
        });
    },
};
