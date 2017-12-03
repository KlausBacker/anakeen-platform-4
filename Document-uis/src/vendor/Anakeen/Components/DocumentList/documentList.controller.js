import anakeenComponentOptions from '../componentBase';
import DocumentTemplate from './documentItem.template.kd';

export default {
    mixins: [anakeenComponentOptions],
    props: {
        logoUrl: {
            type: String,
            default: 'CORE/Images/anakeen-logo.svg',
        },
    },

    created() {
        this.privateScope = {
            replaceTopPagerButton: () => {
                const $pager = this.$(this.$refs.summaryPager);
                const buttons = $pager.find('.k-pager-nav:not(.k-pager-last):not(.k-pager-first)');
                const label = $pager.find('span.k-pager-info');
                label.insertBefore(buttons[1]);
            },

            initKendo: () => {
                const _this = this;
                this.dataSource = new this.$kendo.data.DataSource({
                    transport: {
                        read: (options) => {
                            if (options.data.collection) {
                                const params = {
                                    fields: 'document.properties.state,document.properties.icon',
                                    page: options.data.page,
                                    offset: (options.data.page - 1) * options.data.take,
                                    slice: options.data.take,
                                };
                                if (this.filterInput) {
                                    params.filter = this.filterInput;
                                }

                                _this.privateScope
                                    .sendGetRequest(`api/v1/sba/collections/${options.data.collection}/documentsList`,
                                        {
                                            params,
                                        })
                                    .then((response) => {
                                        options.success(response);
                                    }).catch((response) => {
                                    options.error(response);
                                });
                            } else {
                                options.error();
                            }
                        },
                    },
                    pageSize: this.pageSizeOptions[1].value,
                    serverPaging: true,
                    schema: {
                        total: (response) => response.data.data.resultMax,

                        data: (response) => response.data.data.documents,
                    },

                });
                this.$(this.$refs.listView).kendoListView({
                    dataSource: this.dataSource,
                    template: this.$kendo.template(DocumentTemplate),
                    selectable: 'single',
                    change: this.privateScope.onSelectDocument,
                    scrollable: true,
                });

                this.$(this.$refs.pager).kendoPager({
                    dataSource: this.dataSource,
                    numeric: false,
                    input: true,
                    info: false,
                    pageSizes: false,
                    change: this.privateScope.onPagerChange,
                    messages: {
                        page: '',
                        of: '/ {0}',
                        itemsPerPage: this.$pgettext('DocumentList', 'Items per page'),
                    },
                });
                this.$(this.$refs.summaryPager).kendoPager({
                    dataSource: this.dataSource,
                    numeric: false,
                    input: false,
                    info: true,
                    change: this.privateScope.onPagerChange,
                    messages: {
                        display: `{0} - {1} ${this.$pgettext('DocumentList', 'of')} {2}`,
                    },
                });

                this.$(this.$refs.pagerCounter).kendoDropDownList({
                    dataSource: this.pageSizeOptions,
                    dataTextField: 'text',
                    dataValueField: 'value',
                    animation: false,
                    index: 1,
                    change: this.privateScope.onSelectPageSize,
                    // valueTemplate: '<span class="fa fa-list-ol"></span>',
                    headerTemplate: `<li class="dropdown-header">${this.$pgettext('DocumentList', 'Items per page')}</li>`,
                    template: '<span class="documentsList__documents__pagination__pageSize">#= data.text#</span>',
                }).data('kendoDropDownList').list.addClass('documentsList__documents__pagination__list');
            },

            onPagerChange: (e) => {
                this.dataSource.page(e.index);
                this.refreshDocumentsList();
            },

            sendGetRequest: (url, conf) => {
                const element = this.$(this.$refs.wrapper);
                this.$kendo.ui.progress(element, true);
                return new Promise((resolve, reject) => {
                    this.$http.get(url, conf)
                        .then((response) => {
                            this.$kendo.ui.progress(element, false);
                            resolve(response);
                        }).catch((error) => {
                        this.$kendo.ui.progress(element, false);
                        reject(error);
                    });
                });
            },

            onSelectPageSize: (e) => {
                const counter = this.$(this.$refs.pagerCounter).data('kendoDropDownList');
                const newPageSize = counter.dataItem(e.item).value;
                this.dataSource.pageSize(newPageSize);
                this.refreshDocumentsList();
            },

            onSelectDocument: (...arg) => {
                // this.$emit('store-save', {action: 'openDocument', data: document });
                const data = this.dataSource.view();
                const listView = this.$(this.$refs.listView).data('kendoListView');
                const selected = this.$.map(listView.select(), item => data[this.$(item).index()]);
                this.selectDocument(selected[0]);
            },
        };
    },

    mounted() {
        this.$kendo.ui.progress(this.$(this.$refs.wrapper), true);
        const ready = () => {
            this.privateScope.initKendo();
            this.privateScope.replaceTopPagerButton();
            this.$kendo.ui.progress(this.$(this.$refs.wrapper), false);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ready);
        } else {
            ready();
        }
    },

    data() {
        return {
            collection: null,
            documents: [],
            appConfig: null,
            dataSource: null,
            filterInput: '',
            pageSizeOptions: [
                {
                    text: '5',
                    value: 5,
                },
                {
                    text: '10',
                    value: 10,
                },
                {
                    text: '25',
                    value: 25,
                },
                {
                    text: '50',
                    value: 50,
                },
                {
                    text: '100',
                    value: 100,
                },
            ],
        };
    },

    computed: {
        translations() {
            return {
                searchPlaceholder: this.$pgettext('DocumentList', `Search in `) + (this.collection ? this.collection.html_label : ''),
            };
        },
    },

    methods: {

        selectDocument(document) {
            this.$emit('document-selected', Object.assign({}, document.properties));
        },

        filterDocumentsList(filterValue) {
            this.filterInput = filterValue;
            if (filterValue) {
                this.refreshDocumentsList();
            } else {
                this.clearDocumentsListFilter();
            }
        },

        clearDocumentsListFilter() {
            this.filterInput = '';
            this.refreshDocumentsList();
        },

        setCollection(c) {
            this.collection = c;
            this.dataSource.page(1);
            this.refreshDocumentsList();
        },

        refreshDocumentsList() {
            return new Promise((resolve, reject) => {
                if (this.collection && this.dataSource) {
                    this.dataSource.read({ collection: this.collection.initid }).then(resolve).catch(reject);
                } else {
                    reject();
                }
            });
        },
    },
};
