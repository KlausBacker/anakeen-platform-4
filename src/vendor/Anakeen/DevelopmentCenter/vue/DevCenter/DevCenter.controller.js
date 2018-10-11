import Vue from "vue";
import { AnkNotifier } from "@anakeen/ank-components";
import DevHeader from "./DevHeader/DevHeader.vue";
import DevSideMenu from "./DevSideMenu/DevSideMenu.vue";

import ErrorManager from "./utils/ErrorManager";
Vue.use(AnkNotifier);

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
    this.errorManager.bindNotifier();
    this.errorManager.bindNetworkCommonsErrors();
  }
};
