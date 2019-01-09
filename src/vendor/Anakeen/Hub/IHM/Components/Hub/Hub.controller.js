import { AnkDock } from "@anakeen/ank-components";
import { AnkLogout } from "@anakeen/ank-components";
import { AnkIdentity } from "@anakeen/ank-components";

export default {
  name: "ank-hub",
  components: {
    dock: AnkDock,
    logout: AnkLogout,
    identity: AnkIdentity
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
          this.content = response.data.data;
        })
        .catch(error => {
          // TODO Notify user
          console.error(error);
        });
    }
  }
};
