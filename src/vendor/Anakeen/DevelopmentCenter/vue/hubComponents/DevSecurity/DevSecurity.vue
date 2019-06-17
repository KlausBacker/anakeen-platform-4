<template>
    <div>
        <nav v-if="isDockCollapsed || isDockExpanded">
            <span>Security</span>
        </nav>
        <div v-else-if="isHubContent" class="dev-security">
            <dev-security
                    :securitySection="securitySection"
                    :ssName="ssName"
                    :ssSection="ssSection"
                    :wflName="wflName"
                    :wflSection="wflSection"
                    :routeAccess="routeAccess"
                    :profile="profile"
                    :fieldAccess="fieldAccess"
                    :role="role"
                    @navigate="onNavigate"
            ></dev-security>

        </div>
    </div>
</template>
<script>
  import HubElement from "@anakeen/hub-components/components/lib/HubElement";
  import { setupVue } from "../../setup.js";
  import securityStore from "./storeModule.js";
  export default {
    name: "ank-dev-security",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      "dev-security": () =>
        new Promise(resolve => {
          import("../../sections/Security/SecurityDevCenter.vue").then(Component => {
            resolve(Component.default);
          });
        })
    },
    beforeCreate() {
      if (this.$options.propsData.displayType === "COLLAPSED") {
        this.$parent.$parent.collapsable = false;
        this.$parent.$parent.collapsed = false;
      }
    },
    computed: {
      securitySection() {
        return this.$store.getters["security/securitySection"];
      },
      ssName() {
        return this.$store.getters["security/ssName"];
      },
      ssSection() {
        return this.$store.getters["security/ssSection"];
      },
      wflName() {
        return this.$store.getters["security/wflName"];
      },
      wflSection() {
        return this.$store.getters["security/wflSection"];
      },
      routeAccess() {
        return this.$store.getters["security/routeAccess"];
      },
      profile() {
        return this.$store.getters["security/profile"];
      },
      fieldAccess() {
        return this.$store.getters["security/fieldAccess"];
      },
      role() {
        return this.$store.getters["security/role"];
      }
    },
    created() {
      if (this.isHubContent) {
        setupVue(this);
        if (this.$store) {
          this.$store.registerModule(["security"], securityStore);
        }
        const pattern = `/${this.entryOptions.route}(?:/(\\w+)(?:/(\\w+)(?:/(\\w+))?)?)?`;
        this.getRouter().on(new RegExp(pattern), (...params) => {
          const securitySection = params[0];
          this.$store.dispatch("security/setSecuritySection", securitySection);
          if (securitySection === "smartStructures") {
            this.$store.dispatch("security/setStructureName", params[1]);
            this.$store.dispatch("security/setStructureSection", params[2]);
          } else if (securitySection === "workflows") {
            this.$store.dispatch("security/setWflName", params[1]);
            this.$store.dispatch("security/setWflSection", params[2]);
          } else if (securitySection === "routes") {
            this.$store.dispatch("security/setRouteAccess", params[1]);
          } else if (securitySection === "profiles" && params[1]) {
            this.$store.dispatch("security/setProfile", params[1])
          } else if (securitySection === "fieldAccess" && params[1]) {
            this.$store.commit("security/SET_FIELD_ACCESS", {
              url: `${params[1]}/${params[2]}`,
              component: `fall-${params[2]}`,
              props: {
                onlyExtendedAcls: true,
                profileId: params[1],
                fallid: params[1]
              },
              name: params[1],
              label: params[1]
            })
          } else if (securitySection === "roles" && params[1]) {
            this.$store.commit("security/SET_ROLE", params[1]);
          }
        }).resolve();
      }
    },
    methods: {
      onNavigate(route) {
        const routeUrl = `/devel/${this.entryOptions.route}/`+route.map(r => r.url).join("/").replace(/\/\//g, '/');
        this.navigate(routeUrl);
      }
    }
  };
</script>
<style>
    .dev-security {
        flex: 1;
        display: flex;
        min-height: 0;
        height: 100%;
        width: 100%;
    }
</style>
