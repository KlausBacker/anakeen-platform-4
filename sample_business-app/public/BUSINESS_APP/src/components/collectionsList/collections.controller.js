export default {
  data() {
    return {
      showCollections: true
    };
  },
  methods: {
    onToggleCollections() {
      this.showCollections = !this.showCollections;
    }
  }
}