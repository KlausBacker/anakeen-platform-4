export default {
  props: {
    logoUrl: {
      type: String,
      default: 'CORE/Images/anakeen-logo.svg',
    },
  },

  mounted() {
    document.addEventListener('DOMContentLoaded', (event) => {
      const store = document.getElementById('a4-store');
      if (store) {
        store.addEventListener('store-change', (event) => {
          const storeData = event.detail && event.detail.length ? event.detail[0] : null;
          this.onStoreChange(storeData);
        });
      }
    });
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
      this.$emit('store-save', { action: 'openDocument', data: document });
      this.$emit('document-selected', document);
    },

    onStoreChange(storeData) {
      if (storeData) {
        switch (storeData.type) {
          case 'SELECT_COLLECTION':
            this.collection = storeData.data;
            this.sendGetRequest(`/sba/collections/${this.collection.ref}/documentsList`)
              .then((response) => {
              this.documents = response.data.data.sample;
              this.updateKendoData();
            });
            break;
        }
      }
    },

    initKendo() {
      this.dataSource = new this.$kendo.data.DataSource({
        data: [],
        pageSize: this.pageSizeOptions[1].value,
      });
      this.$(this.$refs.listView).kendoListView({
        dataSource: this.dataSource,
        template: this.$kendo.template('<div class="documentsList__documentCard"><div class="documentsList__documentCard__body"><div class="documentsList__documentCard__heading">' +
          '<img class="documentsList__documentCard__heading__content_icon" src="#: collection.image_url#"  alt="#: title# image"/>' +
          '<span>#:title#</span>' +
          '</div></div></div>'),
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
        valueTemplate: '<span class="fa fa-list-ol"></span>',
        template: '<span class="documentsList__documents__pagination__pageSize">#= data.text#</span>',
      });

      this.$(this.$refs.removeFilterButton).kendoButton({
        icon: 'close',
        click: this.onRemoveClick,
      });
      this.$(this.$refs.searchFilterButton).kendoButton({
        icon: 'search',
        click: this.onSearchClick,
      });
      this.updateKendoData();
    },

    updateKendoData() {
      this.dataSource.data(this.documents.map((d) => {
        d.collection = this.collection;
        return d;
      }));
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
