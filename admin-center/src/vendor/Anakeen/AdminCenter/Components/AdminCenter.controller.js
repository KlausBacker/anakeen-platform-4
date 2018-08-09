import AdminHeader from "./AdminHeader/AdminHeader.vue";
import AdminContent from "./AdminContent/AdminContent.vue";
import AdminModal from "./AdminModal/AdminModal.vue";

import { mapActions } from "vuex";
export default {
  components: {
    AdminHeader,
    AdminContent,
    AdminModal
  },
  mounted() {
    this.loadPluginsList();
  },
  methods: {
    ...mapActions(["loadPluginsList"])
  }
};
