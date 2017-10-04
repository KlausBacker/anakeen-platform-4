import store from './config';

export default {
  store: store,
  mounted() {
    this.$store.subscribe((mutation, state) => {
      this.fireStoreChangeEvent(mutation, state);
    });
    document.addEventListener("DOMContentLoaded", (event) => {
      const components = document.getElementsByClassName('a4-component');
      for (let i = 0; i < components.length; i++) {
        components[i].addEventListener('store-save', (event) => {
          const action = event.detail && event.detail.length ? event.detail[0] : null;
          this.sendAction(action);
        })
      }
    });
  },
  methods: {
    sendAction(action) {
      if (action) {
        this.$store.dispatch(action.action, action.data);
      }
    },
    fireStoreChangeEvent(mutation, state) {
      this.$emit('store-change', { type: mutation.type, data: mutation.payload, state});
    }
  }
};