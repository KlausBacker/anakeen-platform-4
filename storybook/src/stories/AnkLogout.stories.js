import Vue from "vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import AnkLogout from "@anakeen/user-interfaces/components/lib/AnkLogout.esm";

import AnkLogoutVue from "../../../user-interfaces/components/src/AnkLogout/AnkLogout.vue";

import { action } from "@storybook/addon-actions";

import "../../../user-interfaces/components/scss/bootstrap.scss";
import "../../../user-interfaces/components/scss/kendo.scss";

Vue.use(setup);

export default {
  title: "Ui Component/Ank Logout",
  component: AnkLogoutVue,
  argTypes: {
    title: { control: "text", description: "Libellé affiché au survol du bouton" },
    afterLogout: { action: "afterLogout", description: "Événement déclenché après la requête de déconnexion" }
  }
};

const Template = (args, { argTypes }) => ({
  props: Object.keys(argTypes),

  components: {
    "ank-logout": () => {
      return Vue.$_globalI18n.recordCatalog().then(() => {
        return AnkLogout;
      });
    }
  },
  template: '<ank-logout  v-on="listeners" v-bind="$props" />',
  computed: {
    listeners: function() {
      const listenOn = {};
      for (const [key, value] of Object.entries(argTypes)) {
        if (value.table && value.table.category === "events") {
          listenOn[key] = (e, ...o) => {
            action(key)(e, ...o);
          };
        }
      }

      return listenOn;
    }
  }
});

export const SimpleLogout = Template.bind({});
SimpleLogout.storyName = "Déconnexion";
SimpleLogout.args = {};
export const LabelLogout = Template.bind({});
LabelLogout.storyName = "Avec libellé personnalisé";
LabelLogout.args = {
  title: "Hop là"
};
