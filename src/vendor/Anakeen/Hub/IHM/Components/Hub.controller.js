import { AnkDock } from "@anakeen/ank-components";

const dock = AnkDock;

export default {
  name: "ank-hub",
  components: {
    dock
  },
  data() {
    return {
      content: []
    };
  },
  mounted() {
    this.getConfig();
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
  }
};
