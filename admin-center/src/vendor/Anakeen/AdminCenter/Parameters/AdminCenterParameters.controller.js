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
        };
    },

    methods: {
        initTreeList() {
            let toolbarTemplate = `
                <div class="global-parameters-toolbar">
                    <button class="btn btn-secondary refresh-btn"><i class="material-icons">refresh</i></button>
                    <button class="btn btn-secondary expand-btn"><i class="material-icons">expand_more</i></button>
                    <button class="btn btn-secondary collapse-btn"><i class="material-icons">expand_less</i></button>
                    <div id="search-input" class="input-group">
                        <input type="text" class="form-control global-search-input" placeholder="Filter parameters...">
                        <div class="input-group-append">
                            <button class="btn btn-secondary reset-search-btn" type="button">Reset</button>
                        </div>
                    </div>
                </div>
            `;

            this.$('#parameters-tree').kendoTreeList({
                dataSource: this.allParametersDataSource,
                columns: [
                    { field: 'name', title: 'Name', width: '40%', },
                    { field: 'description', title: 'Description', width: '50%' },
                    { field: 'value', title: 'Value', width: '10%' },
                    {
                        width: '8%',
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
                },

                collapse: (e) => {
                    this.addClassToRow(e.sender);
                },

                dataBound: (e) => {
                    this.addClassToRow(e.sender);
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
            } else {
                this.allParametersDataSource.filter({});
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
            let treeList = $('#parameters-tree').data('kendoTreeList');
            let $rows = $('tr.k-treelist-group', treeList.tbody);
            $.each($rows, (idx, row) => {
                if (expansion) {
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
        window.addEventListener('resize', () => {
            let $tree = this.$('#parameters-tree');
            let kTree = $tree.data('kendoTreeList');

            if (kTree) {
                $tree.height($(window).height() - $tree.offset().top - 4);
                kTree.resize();
            }
        });
    },
};
