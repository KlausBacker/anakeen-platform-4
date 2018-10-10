export default {
  mounted() {

  },
  computed: {
    sections() {
      return this.$router.options.routes[0].children;
    }
  }
};
