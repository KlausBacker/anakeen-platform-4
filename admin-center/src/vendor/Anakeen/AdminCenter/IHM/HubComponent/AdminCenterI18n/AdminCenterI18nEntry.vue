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
          :i18nFilters="i18nFilters"
          :lang="lang"
        ></admin-center-i18n>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHub.esm";
import { Watch } from "vue-property-decorator";

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
      i18nFilters: [],
      lang: "",
      routeUrl: () => {
        return "/" + this.entryOptions.route;
      },
      subRouting: () => {
        const url = (this.routeUrl() + "/:lang").replace(/\/\/+/g, "/");
        this.getRouter()
          .on(url, parameters => {
            this.lang = parameters.lang;
            this.i18nFilters = [];
            const re = /(section|msgctxt|msgid|msgstr)=([^&]+)/g;
            let match;
            while ((match = re.exec(window.location.search))) {
              if (match && match.length >= 3) {
                const field = match[1];
                const value = decodeURIComponent(match[2]);
                switch (field) {
                  case "section": {
                    this.i18nFilters.push({
                      field: "section",
                      operator: "equals",
                      value: value
                    });
                    break;
                  }
                  case "msgctxt": {
                    this.i18nFilters.push({
                      field: "msgctxt",
                      operator: "equals",
                      value: value
                    });
                    break;
                  }
                  case "msgid": {
                    this.i18nFilters.push({
                      field: "msgid",
                      operator: "equals",
                      value: value
                    });
                    break;
                  }
                  case "msgstr": {
                    this.i18nFilters.push({
                      field: "msgstr",
                      operator: "equals",
                      value: value
                    });
                    break;
                  }
                }
              }
            }
          })
          .resolve(window.location.pathname);
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
      });
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
