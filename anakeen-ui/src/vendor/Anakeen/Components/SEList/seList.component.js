import { AnkMixin } from '../AnkVueComponentMixin';
import SeTemplate from './seListItem.template.kd';

export default {
    mixins: [AnkMixin],
    props: {
        logoUrl: {
            type: String,
            default: '/CORE/Images/anakeen-logo.svg',
        },
        smartCollection: {
            default: '',
        },
        label: {
            default: '',
        },
        contentUrl: {
            type: String,
            default: '/components/selist/pager/{collection}/pages/{page}',
        },
        order: {
            type: String,
            default: 'title:asc',
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

            propageKendoDataSourceEvent: (eventName) => (e) => {
                const customEvent = this.$createComponentEvent(`se-list-${eventName}`,
                    { cancelable: true, detail: [e] });
                const notCancelled = this.$emitAnkEvent(`se-list-${eventName}`, customEvent);
                if (!notCancelled) {
                    if (e.preventDefault) {
                        e.preventDefault();
                    }
                }
            },

            initKendo: () => {
                const _this = this;
                this.dataSource = new this.$kendo.data.DataSource({
                    error: this.privateScope.propageKendoDataSourceEvent('error'),
                    requestStart: this.privateScope.propageKendoDataSourceEvent('request-start'),
                    requestEnd: this.privateScope.propageKendoDataSourceEvent('request-end'),
                    change: this.privateScope.propageKendoDataSourceEvent('change'),
                    transport: {
                        read: (options) => {
                            if (options.data.collection) {
                                const params = {
                                    slice: options.data.take,
                                    orderBy: this.orderBy,
                                };
                                if (this.filterInput) {
                                    params.filter = this.filterInput;
                                }

                                const request = this.contentUrl.
                                replace('{collection}', options.data.collection).
                                replace('{page}', options.data.page);
                                _this.privateScope
                                    .sendGetRequest(request,
                                        {
                                            params,
                                        })
                                    .then((response) => {
                                        const apiData = response.data.data;
                                        if (apiData && apiData.collection && apiData.collection.properties) {
                                            _this.collection = Object.assign({}, _this.collection, apiData.collection.properties);
                                        }

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
                    template: this.$kendo.template(SeTemplate),
                    selectable: 'single',
                    change: this.privateScope.onSelectSe,
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
                        empty: this.translations.noDataPagerLabel,
                    },
                });
                this.$(this.$refs.summaryPager).kendoPager({
                    dataSource: this.dataSource,
                    numeric: false,
                    input: false,
                    info: true,
                    change: this.privateScope.onPagerChange,
                    messages: {
                        display: `{0} - {1} ${this.$pgettext('SEList', 'of')} {2}`,
                        empty: this.translations.noDataPagerLabel,
                    },
                });

                this.$(this.$refs.pagerCounter).kendoDropDownList({
                    dataSource: this.pageSizeOptions,
                    dataTextField: 'text',
                    dataValueField: 'value',
                    animation: false,
                    index: 1,
                    change: this.privateScope.onSelectPageSize,
                    headerTemplate: `<li class="dropdown-header">${this.translations.itemsPerPageLabel}</li>`,
                    template: '<span class="seList__pagination__pageSize">#= data.text#</span>',
                }).data('kendoDropDownList').list.addClass('seList__pagination__list');
            },

            onPagerChange: (e) => {
                const oldPage = this.dataSource.page();
                this.dataSource.page(e.index);
                const newPage = this.dataSource.page();
                this.$emitAnkEvent('se-list-page-change', oldPage, newPage);
                this.refreshList().then().catch((err) => {
                    console.error(err);
                });
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
                const customEvent = this.$createComponentEvent('se-list-pagesize-change',
                    { detail: [newPageSize, this.dataSource.pageSize()] }, e);
                this.$emitAnkEvent('se-list-pagesize-change', customEvent);
                this.dataSource.pageSize(newPageSize);
                this.refreshList().then().catch((err) => {
                    console.error(err);
                });
            },

            onSelectSe: (event) => {
                const data = this.dataSource.view();
                const listView = this.$(this.$refs.listView).data('kendoListView');
                const selected = this.$.map(listView.select(), item => data[this.$(item).index()]);
                this._selectSe(event, selected[0]);
            },
        };
    },

    mounted() {
        this.$kendo.ui.progress(this.$(this.$refs.wrapper), true);
        const ready = () => {
            this.privateScope.initKendo();
            this.privateScope.replaceTopPagerButton();
            this.$kendo.ui.progress(this.$(this.$refs.wrapper), false);

            if (this.smartCollection) {
                this.setCollection({
                    title: this.collectionLabel,
                    name: this.smartCollection,
                });
            }
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
            dataSource: null,
            filterInput: '',
            orderBy: this.order,
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
            const searchTranslated = this.$pgettext('SEList', 'Search in : %{collection}');
            const noDataTranslated = this.$pgettext('SEList', 'No %{collection} to display');
            return {
                searchPlaceholder: this.$gettextInterpolate(searchTranslated, {
                        collection: this.collectionLabel.toUpperCase(),
                    }),
                itemsPerPageLabel: this.$pgettext('SEList', 'Items per page'),
                noDataPagerLabel: this.$gettextInterpolate(noDataTranslated, {
                        collection: this.collectionLabel,
                    }),
            };
        },

        collectionLabel() {
            if (this.collection && this.collection.title) {
                return this.collection.title;
            } else if (this.label) {
                return this.label;
            } else {
                return '';
            }
        },
    },

    methods: {

        _onFilterInput(event) {
            const customEvent = this.$createComponentEvent('se-list-filter-input',
                { detail: [this.filterInput] }, event);
            this.$emitAnkEvent('se-list-filter-input', customEvent);
        },

        _selectSe(event, se) {
            const seProperties = Object.assign({}, se.properties);
            const customEvent = this.$createComponentEvent('se-selected', { detail: [seProperties] }, event);
            this.$emitAnkEvent('se-selected', customEvent);
        },

        filterList(filterValue) {
            this.filterInput = filterValue;
            if (filterValue) {
                this.refreshList().then().catch((err) => {
                    console.error(err);
                });
            } else {
                this.clearListFilter();
            }
        },

        clearListFilter() {
            this.filterInput = '';
            this.refreshList().then().catch((err) => {
                console.error(err);
            });
        },

        setCollection(c, opts = null) {
            this.collection = c;
            if (opts && opts.order) {
                this.orderBy = opts.order;
            } else {
                this.orderBy = 'title:asc';
            }

            this.dataSource.page(1);
            this.refreshList().then().catch((err) => {
                console.error(err);
            });
        },

        refreshList(opts = {}) {
            return new Promise((resolve, reject) => {
                if (this.collection && this.dataSource) {
                    this.dataSource.read({ collection: this.collection.initid || this.collection.name })
                        .then(resolve).catch(reject);
                } else {
                    reject();
                }
            });
        },
    },
};