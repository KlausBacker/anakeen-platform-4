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
                this.dataSource = new this.$kendo.data.DataSource({
                    data: [],
                    pageSize: this.pageSizeOptions[1].value,
                });
                this.$(this.$refs.listView).kendoListView({
                    dataSource: this.dataSource,
                    template: this.$kendo.template(DocumentTemplate),
                    selectable: 'multiple',
                    change: this.onSelectDocument,
                });

                this.$(this.$refs.pager).kendoPager({
                    dataSource: this.dataSource,
                    numeric: false,
                    input: true,
                    info: false,
                    messages: {
                        page: '',
                        of: '/ {0}',
                    },
                });
                this.$(this.$refs.summaryPager).kendoPager({
                    dataSource: this.dataSource,
                    numeric: false,
                    input: false,
                    info: true,
                    messages: {
                        display: '{0} - {1} sur {2}',
                    },
                });

                this.$(this.$refs.pagerCounter).kendoDropDownList({
                    dataSource: this.pageSizeOptions,
                    dataTextField: 'text',
                    dataValueField: 'value',
                    animation: false,
                    index: 1,
                    change: this.onSelectPageSize,
                    // valueTemplate: '<span class="fa fa-list-ol"></span>',
                    headerTemplate: '<li class="dropdown-header">Eléments par page</li>',
                    template: '<span class="documentsList__documents__pagination__pageSize">#= data.text#</span>',
                }).data('kendoDropDownList').list.addClass('documentsList__documents__pagination__list');
                this.privateScope.updateKendoData();
            },

            updateKendoData: () => {
                this.dataSource.data(this.documents);
            },

            sendGetRequest: (url) => {
                const element = this.$(this.$refs.wrapper);
                this.$kendo.ui.progress(element, true);
                return new Promise((resolve, reject) => {
                    this.$http.get(url)
                        .then((response) => {
                            this.$kendo.ui.progress(element, false);
                            resolve(response);
                        }).catch((error) => {
                        this.$kendo.ui.progress(element, false);
                        reject(error);
                    });
                });
            },
        };
    },

    mounted() {
        this.privateScope.initKendo();
        this.privateScope.replaceTopPagerButton();
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

    methods: {
        onSelectDocument(...arg) {
            // this.$emit('store-save', {action: 'openDocument', data: document });
            const data = this.dataSource.view();
            const listView = this.$(this.$refs.listView).data('kendoListView');
            const selected = this.$.map(listView.select(), item => data[this.$(item).index()]);
            this.selectDocument(selected[0]);
        },

        selectDocument(document) {
            this.$emit('document-selected', Object.assign({}, document.properties));
        },

        onSelectPageSize(e) {
            const counter = this.$(this.$refs.pagerCounter).data('kendoDropDownList');
            const newPageSize = counter.dataItem(e.item).value;
            this.dataSource.pageSize(newPageSize);
        },

        onSearchClick() {
            if (this.filterInput) {
                this.privateScope
                    .sendGetRequest(`/sba/collections/${this.collection.ref}/documentsList/filter=${this.filterInput}`)
                    .then((response) => {
                        this.documents = response.data.data.documents;
                        this.privateScope.updateKendoData();
                    });
            }

        },

        onRemoveClick() {
            this.filterInput = '';
            this.privateScope.sendGetRequest(`/sba/collections/${this.collection.ref}/documentsList`)
                .then((response) => {
                    this.documents = response.data.data.documents;
                    this.privateScope.updateKendoData();
                });
        },

        onFilterInput(event) {
            this.filterInput = event.target.value;
        },

        setCollection(c) {
            this.collection = c;
            this.privateScope.sendGetRequest(`/sba/collections/${this.collection.ref}/documentsList?fields=document.properties.state,document.properties.icon`)
                .then((response) => {
                    this.documents = response.data.data.documents;
                    this.privateScope.updateKendoData();
                });
        },
    },
};