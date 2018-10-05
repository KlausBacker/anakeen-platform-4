import { AnkDock } from "@anakeen/ank-components";
import { AnkLogout } from "@anakeen/ank-components";
import { AnkIdentity } from "@anakeen/ank-components";

const dock = AnkDock;
const logout = AnkLogout;
const identity = AnkIdentity;

export default {
  name: "ank-hub",
  components: {
    dock,
    logout,
    identity
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
