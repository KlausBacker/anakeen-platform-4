
<template>
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">restore_from_trash</i>
      <span v-if="!isDockCollapsed">Trash Manager</span>
    </nav>
    <template v-slot:hubContent>
      <div class="trash-manager">
        <admin-center-trash
          @changeLocaleWrongArgument="handleLocaleWrongArgumentError"
          @trashOffline="handleLocaleNetworkError"
          @notify="handleNotification"
          :msgStrValue="msgStrValue"
          :msgIdValue="msgIdValue"
          :lang="lang"
        ></admin-center-trash>
      </div>
    </template>
  </hub-element-layout>
</template>

<script>
import HubElement from "@anakeen/hub-components/components/lib/HubElement";

export default {
  name: "ank-admin-trash-manager",
  extends: HubElement, // ou mixins: [ HubElementMixins ],

  components: {
    "admin-center-trash": () =>
      new Promise(resolve => {
        import("../../trash/AdminCenterTrash.vue").then(Component => {
          resolve(Component.default);
        });
        
      })
  },
  created() {
    this.subRouting();
  },
  data() {
    return {
      msgStrValue: "",
      msgIdValue: "",
      lang: "",
      routeUrl: () => {
        return `trash/`;
      },
      subRouting: () => {
        const url = (this.routeUrl() + "/:lang").replace(/\/\/+/g, "/");

        this.registerRoute(url, parameters => {
          this.lang = parameters.lang;
          const filters = window.location.search.split("&");
          this.msgStrValue = filters[0].split("=")[1];
          this.msgIdValue = filters[1].split("=")[1];
        }).resolve(window.location.pathname);

      }
    };
  },

  methods: {


  // Manually refresh the tree pane
  updateTreeData() {
    let filterTitle;
    if (this.$refs.filterTree.value) {
      filterTitle = this.$refs.filterTree.value.toLowerCase();
    }
    if (filterTitle !== undefined) {
      this.filter(this.groupTree, filterTitle);
    } else {
      this.showAll(this.groupTree);
      this.expandAll();
    }
  },
    handleLocaleWrongArgumentError(message) {
      this.hubNotify({
        type: "error",
        content: {
          textContent: message, // ou htmlContent: "<em>Un message d'information important</em>"
          title: "Wrong locale argument"
        }
      });
    },
    handleNotification(message, title) {
      this.hubNotify({
        type: "error",
        content: {
          textContent: message,
          title: title || "Network error"
        }
      })
    },
    handleLocaleNetworkError(message) {
      this.hubNotify({
        type: "error",
        content: {
          textContent: message,
          title: "Network error"
        }
      });

    }
  }
};
</script>
<style>
.trash-manager {
  display: flex;
  flex: 1;
  flex-direction: column;
  min-height: 0;
  height: 100%;
}
</style>
