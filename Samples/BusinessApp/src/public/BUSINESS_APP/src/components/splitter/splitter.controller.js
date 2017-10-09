export default {
  data() {
    return {
      collection: null,
    };
  },
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
  methods: {
    initKendo() {

    },
    onStoreChange(storeData) {
      if (storeData) {
        switch (storeData.type) {
          case 'SELECT_COLLECTION':
            this.collection = storeData.data;
            break;
        }
      }
    },
  }
}