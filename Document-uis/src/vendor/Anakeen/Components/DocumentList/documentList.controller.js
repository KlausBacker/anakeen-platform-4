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

    mounted() {
        this.initKendo();
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
            this.$emit('document-selected', document);
        },

        initKendo() {
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
                headerTemplate: '<li class="dropdown-header">Eléments par page</li>',
                template: '<span class="documentsList__documents__pagination__pageSize">#= data.text#</span>',
            }).data('kendoDropDownList').list.addClass('documentsList__documents__pagination__list');
            this.updateKendoData();
        },

        updateKendoData() {
            this.dataSource.data(this.documents);
        },

        onSelectPageSize(e) {
            const counter = this.$(this.$refs.pagerCounter).data('kendoDropDownList');
            const newPageSize = counter.dataItem(e.item).value;
            this.dataSource.pageSize(newPageSize);
        },

        onSearchClick() {
            this.dataSource.filter({ field: 'title', operator: 'contains', value: this.filterInput });
        },

        onRemoveClick() {
            this.filterInput = '';
            this.dataSource.filter(null);
        },

        onFilterInput(event) {
            this.filterInput = event.target.value;
        },

        setCollection(c) {
            this.collection = c;
            this.sendGetRequest(`/sba/collections/${this.collection.ref}/documentsList`)
                .then((response) => {
                    this.documents = response.data.data.sample;
                    this.updateKendoData();
                });
        },

        sendGetRequest(url) {
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
    },
};
