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
  },
  data() {
    return {
      collection: null,
      documents: [],
      dataSource: null,
    };
  },
  methods: {
    onClickDocument(event, document) {
      this.$emit('store-save', {action: 'openDocument', data: document });
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
        data: this.documents,
        pageSize: 21
      });
      this.$(this.$refs.listView).kendoListView({
        dataSource: this.dataSource,
        template: this.$kendo.template('<div class="documentsList__documentCard"><div class="documentsList__documentCard__body"><div class="documentsList__documentCard__heading">'+
          (this.collection ? `<img src="${this.collection.image_url}" />` : '')+
          '<span>#:title#</span></div></div></div>')
      });

      this.$(this.$refs.pager).kendoPager({
        dataSource: this.dataSource
      });
    },
    updateKendoData() {
      this.dataSource.data(this.documents);
    }
  }
}