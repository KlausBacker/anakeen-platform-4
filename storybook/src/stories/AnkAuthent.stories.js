import Vue from "vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import AnkAuthent from "@anakeen/user-interfaces/components/lib/AnkAuthent.esm";

import AnkAuthentVue from "../../../user-interfaces/components/src/AnkAuthent/AnkAuthent.vue";

import { action } from "@storybook/addon-actions";

// import "../../../user-interfaces/components/scss/bootstrap.scss";
// import "../../../user-interfaces/components/scss/kendo.scss";

Vue.use(setup);

export default {
  title: "Ui Component/Ank authent",
  component: AnkAuthentVue,
  argTypes: {
    defaultLanguage: {
      control: { type: "inline-radio", options: ["fr-FR", "en-US"] },
      description: "Affichage de langue par défaut"
    }
  }
};

// eslint-disable-next-line no-unused-vars
const Template = (args, { argTypes }) => ({
  props: {
    authentLanguages: { type: String, default: "fr-FR, en-US" },
    defaultLanguage: { type: String, default: "fr-FR" }
  },

  components: {
    "ank-authent": () => {
      return Vue.$_globalI18n.recordCatalog().then(() => {
        return AnkAuthent;
      });
    }
  },
  template:
    '<ank-authent  :default-language="defaultLanguage" :authent-languages="authentLanguages" v-on="listeners"/>',
  methods: {
    // @FIXME: need to add this workaround to se event in action addOns
    // I don't know why but it test if Vue has toJSON method
    toJSON: function() {
      //JSON.stringify;
      return "boo";
    }
  },

  computed: {
    getConfig: function() {
      return JSON.parse(this.configCriteria);
    },

    getOptions: function() {
      return {
        withCloseConfirmation: this.closeConfirmation
      };
    },
    listeners: function() {
      const listenOn = {};

      for (const [key, value] of Object.entries(argTypes)) {
        if (value.table && value.table.category === "events") {
          listenOn[key] = function(ev, ...o) {
            action(key)(ev, ...o);
          };
        }
      }

      return listenOn;
    }
  }
});

export const FrenchAuthent = Template.bind({});
FrenchAuthent.args = {
  defaultLanguage: "fr-FR"
};
export const EnglishAuthent = Template.bind({});
EnglishAuthent.args = {
  defaultLanguage: "en-US"
};
