import AnkNotifier from "@anakeen/internal-components/lib/Notifier";

import DevHeader from "./DevHeader/DevHeader.vue";
import DevSideMenu from "./DevSideMenu/DevSideMenu.vue";

import { interceptDOMLinks } from "../router/utils";
import ErrorManager from "./utils/ErrorManager";

export default {
  name: "dev-center",
  components: {
    DevHeader,
    DevSideMenu,
    AnkNotifier
  },
  created() {
    this.errorManager = new ErrorManager(this);
  },
  mounted() {
    interceptDOMLinks(this.$router, this.$el);
    this.errorManager.bindNotifier();
    this.errorManager.bindNetworkCommonsErrors();
  }
};
