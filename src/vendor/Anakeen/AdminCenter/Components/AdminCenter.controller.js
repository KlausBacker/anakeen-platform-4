import AdminHeader from "./AdminHeader/AdminHeader";
import AdminContent from "./AdminContent/AdminContent";
import AdminModal from "./AdminModal/AdminModal";

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
