import Vue from "vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import AnkIdentity from "@anakeen/user-interfaces/components/lib/AnkIdentity.esm";

//import AnkIdentity from "../../../user-interfaces/components/src/AnkIdentity";
import AnkIdentityVue from "../../../user-interfaces/components/src/AnkIdentity/AnkIdentity.vue";

import { action } from "@storybook/addon-actions";

import "@anakeen/user-interfaces/components/scss/AnkIdentity.scss";
Vue.use(setup);

export default {
  title: "Ui Component/Ank Identity",
  component: AnkIdentityVue,
  argTypes: {
    large: { control: "boolean" },
    emailAlterable: { control: "boolean" },
    passwordAlterable: { control: "boolean" },
    afterUserLoaded: {
      action: "afterUserLoaded",
      description: "Événement déclenché après la requête de pour récupérer le nom et les initiales de l'utilisateur"
    }
  }
};

const Template = (args, { argTypes }) => ({
  props: Object.keys(argTypes),

  components: {
    "ank-identity": () => {
      return Vue.$_globalI18n.recordCatalog().then(() => {
        return AnkIdentity;
      });
    }
  },
  template: '<ank-identity v-on="listeners" v-bind="$props" />',
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

export const SimpleIdentity = Template.bind({});
SimpleIdentity.storyName = "Badge simple";
SimpleIdentity.args = {
  large: false,
  emailAlterable: false,
  passwordAlterable: false
};

export const EmailIdentity = Template.bind({});
EmailIdentity.storyName = "Badge avec changement d'adresse de courriel";
EmailIdentity.args = {
  large: false,
  emailAlterable: true,
  passwordAlterable: false
};

export const PasswordIdentity = Template.bind({});
PasswordIdentity.storyName = "Badge avec changement de mot de passe";
PasswordIdentity.args = {
  large: false,
  emailAlterable: false,
  passwordAlterable: true
};
export const PasswordAndEmailIdentity = Template.bind({});
PasswordAndEmailIdentity.storyName = "Badge avec changement mot de passe et courriel";
PasswordAndEmailIdentity.args = {
  large: false,
  emailAlterable: true,
  passwordAlterable: true
};
