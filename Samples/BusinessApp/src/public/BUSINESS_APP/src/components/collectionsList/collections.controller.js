export default {
  mounted() {
    this.$http.get('/sba/collections')
      .then((response) => {
        this.collections = response.data.data.collections;
      });
    const store = document.getElementById('a4-store');
    store.addEventListener('store-change', (event) => {
      const storeData = event.detail && event.detail.length ? event.detail[0] : null;
      this.onStoreChange(storeData)
    });
  },
  data() {
    return {
      showCollections: true,
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
        if (storeData.type === 'TOGGLE_COLLECTIONS') {
          this.showCollections = storeData.data;
        }
      }
    }
  }
}