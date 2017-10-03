export default {
  mounted() {
    this.$http.get('/sba/collections')
      .then((response) => {
        this.collections = response.data.data.collections;
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
      this.showCollections = !this.showCollections;
    }
  }
}