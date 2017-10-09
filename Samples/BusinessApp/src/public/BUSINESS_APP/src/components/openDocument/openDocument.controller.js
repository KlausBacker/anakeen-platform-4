export default {
  mounted() {
    document.addEventListener("DOMContentLoaded", (event) => {
      const store = document.getElementById('a4-store');
      store.addEventListener('store-change', (event) => {
        const storeData = event.detail && event.detail.length ? event.detail[0] : null;
        this.onStoreChange(storeData);
      });
    });
  },
  data() {
    return {
      urlDocument: null
    }
  },
  methods: {
    onStoreChange(storeData) {
      if (storeData) {
        switch (storeData.type) {

        }
      }
    }
  }
}