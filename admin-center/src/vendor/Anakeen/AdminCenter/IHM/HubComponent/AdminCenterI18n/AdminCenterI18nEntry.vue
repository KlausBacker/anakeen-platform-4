<template>
  <hub-element-layout>
    <nav>
      <i class="fa fa-globe i18n-globe" aria-hidden="true"></i>
      <span v-if="!isDockCollapsed">&nbspI18n</span>
    </nav>
    <template v-slot:hubContent>
      <div class="i18n-station">
        <admin-center-i18n
          @changeLocaleWrongArgument="handleLocaleWrongArgumentError"
          @i18nOffline="handleLocaleNetworkError"
        :msgStrValue="msgStrValue" :msgIdValue="msgIdValue" :lang="lang"></admin-center-i18n>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/HubElement";

  export default {
    name: "ank-admin-i18n",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      "admin-center-i18n": () =>
              new Promise(resolve => {
                import("../../I18n/AdminCenterI18n.vue").then(Component => {
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
          return `admin/i18n/`;
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
      handleLocaleWrongArgumentError(message) {
        this.hubNotify({
          type: "error",
          content: {
            textContent: message, // ou htmlContent: "<em>Un message d'information important</em>"
            title: "Wrong locale argument"
          }
        });
      },
      handleLocaleNetworkError(message) {
        this.hubNotify({
          type: "error",
          content: {
            textContent: message,
            title: "Network error"
          }
        })
      }
    }
  };
</script>
<style>
.i18n-station {
  display: flex;
  flex: 1;
  flex-direction: column;
  min-height: 0;
  height: 100%;
}

.i18n-globe {
  font-size: 24px;
  margin-right: 0.5rem;
}
</style>