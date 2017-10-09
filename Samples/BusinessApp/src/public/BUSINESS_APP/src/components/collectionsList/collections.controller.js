export default {
  mounted() {
    this.$http.get('/sba/collections')
      .then((response) => {
        this.collections = response.data.data.sample.collections;
        this.updateKendoData();
        this.selectCollection(this.collections[0]);
      });
    const store = document.getElementById('a4-store');
    store.addEventListener('store-change', (event) => {
      const storeData = event.detail && event.detail.length ? event.detail[0] : null;
      this.onStoreChange(storeData);
    });
    this.initKendo();
  },

  data() {
    return {
      showCollections: true,
      selectedCollection: null,
      collections: [],
      dataSources: null,
      buttons: [
        /*{
          id: 'notif',
          icon: 'fa fa-bell',
          title: 'Notifications',
        },
        {
          id: 'settings',
          icon: 'fa fa-cog',
          title: 'Paramètres',
        },
        {
          id: 'state',
          icon: 'fa fa-refresh',
          title: 'Synchronisé'
        },*/
        {
          id: 'disconnect',
          icon: 'fa fa-power-off',
          title: 'Déconnexion',
          click: () => {
            window.location.href = '?app=CORE&action=LOGOUT';
          },
        },
      ],
    };
  },

  methods: {
    onToggleCollections() {
      this.$emit('store-save', { action: 'toggleCollections', data: !this.showCollections });
    },

    onStoreChange(storeData) {
      if (storeData) {
        switch (storeData.type) {
          case 'TOGGLE_COLLECTIONS':
            this.showCollections = storeData.data;
            break;
          case 'SELECT_COLLECTION':
            this.selectedCollection = storeData.data;
            break;
        }
      }
    },

    selectCollection(c) {
      this.$emit('store-save', { action: 'selectCollection', data: c });
      this.$emit('store-save', { action: 'toggleCollections', data: false });
    },

    initKendo() {
      this.dataSource = new this.$kendo.data.DataSource({
        data: [],
        pageSize: 10,
      });

      this.$(this.$refs.listView).kendoListView({
        dataSource: this.dataSource,
        template: this.$kendo.template('<div class="documentsList__collectionCard">' +
            '<div class="documentsList__collectionCard__body">' +
            '<div class="documentsList__collectionCard__heading">' +
            '<div class="documentsList__collectionCard__heading__content_icon">' +
            '<img src="#: image_url#"  alt="#: html_label# image"/></div>' +
            '<span class="documentsList__collectionCard__heading__content_label">#:html_label#</span>' +
            '</div></div></div>'),
        selectable: 'single',
        change: this.onSelectItemList,
      });

      this.updateKendoData();
    },

    updateKendoData() {
      this.dataSource.data(this.collections);
    },

    onSelectItemList() {
      const data = this.dataSource.view();
      const listView = this.$(this.$refs.listView).data('kendoListView');
      const selected = this.$.map(listView.select(), item => data[this.$(item).index()]);
      this.selectCollection(selected[0]);
    },
  },
};
