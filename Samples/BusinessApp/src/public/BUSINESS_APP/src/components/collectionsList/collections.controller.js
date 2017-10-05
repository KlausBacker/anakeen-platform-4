export default {
  mounted() {
    this.$http.get('/sba/collections')
      .then((response) => {
        this.collections = response.data.data.sample.collections;
        this.selectCollection(this.collections[0]);
      });
    const store = document.getElementById('a4-store');
    store.addEventListener('store-change', (event) => {
      const storeData = event.detail && event.detail.length ? event.detail[0] : null;
      this.onStoreChange(storeData);
    });
  },
  data() {
    return {
      showCollections: true,
      selectedCollection: null,
      collections: [],
      buttons: [
        {
          id: 'notif',
          icon: 'fa fa-bell',
          title: 'Notifications'
        },
        {
          id: 'settings',
          icon: 'fa fa-cog',
          title: 'Paramètres'
        },
        {
          id: 'state',
          icon: 'fa fa-refresh',
          title: 'Synchronisé'
        },
        {
          id: 'disconnect',
          icon: 'fa fa-power-off',
          title: 'Déconnexion'
        },
      ]
    };
  },
  methods: {
    onToggleCollections() {
      this.$emit('store-save', {action: 'toggleCollections', data: !this.showCollections });
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
    onClickCollection(event, collection) {
      this.selectCollection(collection);
    },
    selectCollection(collection) {
      this.$emit('store-save', { action: 'selectCollection', data: collection});
      this.$emit('store-save', { action: 'toggleCollections', data: false});
    }
  }
}