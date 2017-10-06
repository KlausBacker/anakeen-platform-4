export default {
  mounted() {
    document.addEventListener("DOMContentLoaded", (event) => {
      const store = document.getElementById('a4-store');
      store.addEventListener('store-change', (event) => {
        const storeData = event.detail && event.detail.length ? event.detail[0] : null;
        this.onStoreChange(storeData);
      });
    });
    this.initKendo();
    console.log($);
  },
  data() {
    return {
      collection: null,
      documents: [],
      dataSource: null,
    };
  },
  methods: {
    onSelectDocument(...arg) {
      // this.$emit('store-save', {action: 'openDocument', data: document });
      console.log(...arg);
    },
    onStoreChange(storeData) {
      if (storeData) {
        switch (storeData.type) {
          case 'SELECT_COLLECTION':
            this.collection = storeData.data;
            this.$http.get(`/sba/collections/${this.collection.ref}/documentsList`)
              .then((response) => {
              this.documents = response.data.data.sample;
              this.updateKendoData();
            });
            break;
        }
      }
    },
    onClickCollection(event, collection) {
      this.$emit('store-save', { action: 'selectCollection', data: collection});
    },
    initKendo() {
      this.dataSource = new this.$kendo.data.DataSource({
        data: [],
        pageSize: 10
      });
      this.$(this.$refs.listView).kendoListView({
        dataSource: this.dataSource,
        template: this.$kendo.template('<div class="documentsList__documentCard"><div class="documentsList__documentCard__body"><div class="documentsList__documentCard__heading">'+
          '<img class="documentsList__documentCard__heading__content_icon" src="#: collection.image_url#"  alt="#: title# image"/>'+
          '<span>#:title#</span>' +
          '</div></div></div>'),
        selectable: 'multiple',
        change: this.onSelectDocument
      });

      this.$(this.$refs.pager).kendoPager({
        dataSource: this.dataSource,
        numeric: false,
        input: true,
        info: false,
        messages: {
          page: '',
          of: '/ {0}'
        }
      });
      this.$(this.$refs.summaryPager).kendoPager({
        dataSource: this.dataSource,
        numeric: false,
        input: false,
        info: true,
        messages: {
          display: "{0} - {1} sur {2}",
        }
      });
      this.updateKendoData();
    },
    updateKendoData() {
      this.dataSource.data(this.documents.map((d) => {
        d.collection = this.collection;
        return d;
      }));
    }
  }
}