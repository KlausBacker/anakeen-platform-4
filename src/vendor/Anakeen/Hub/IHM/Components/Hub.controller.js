export default {
  name: "ank-hub",

  data() {
    return {
      content: []
    };
  },

  methods: {
    getConfig() {
      this.$http
        .get("/hub/config/")
        .then(response => {
          this.content = response.data;
        })
        .catch(error => {
          // TODO Notify user
          console.error(error);
        });
    }
  },

  mounted() {
    this.getConfig();
  }
};
