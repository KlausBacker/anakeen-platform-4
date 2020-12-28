/* eslint-disable no-unused-vars */
import Vue from "vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm.esm";

//import AnkSmartForm from "../../../user-interfaces/components/src/AnkSmartForm";
import AnkSmartFormVue from "../../../user-interfaces/components/src/AnkSmartForm/AnkSmartForm.vue";

import "@anakeen/user-interfaces/components/scss/AnkSmartElement.scss";

Vue.use(setup);

export default {
  title: "Ui Component/Smart Form",
  component: AnkSmartFormVue,
  argTypes: {
    configForm: { description: "Configuration du <b>Smart Form</b>", control: { type: "text" } },
    config: { description: "Configuration du <b>Smart Form</b>", control: { type: "object" } },

    options: { description: "Options avancées de configuration", control: { type: "object" } },
    initid: { table: { disable: true } },
    viewId: { table: { disable: true } },
    revision: { table: { disable: true } },
    autoUnload: { table: { disable: true } },
    browserHistory: { table: { disable: true } },
    beforeRender: {
      name: "beforeRender",
      type: { name: "void" },
      table: { category: "events" },
      description: "Événement déclenché avant l'éxécution de la fonction de la génération de la DOM"
    },
    smartFieldReady: {
      name: "smartFieldReady",
      type: { name: "void" },
      table: { category: "events" },
      description: "Événement déclenché avant l'éxécution de la fonction de la génération du champ"
    }
  }
};

// noinspection JSUnusedLocalSymbols
const Template = (args, { argTypes }) => ({
  props: {
    closeConfirmation: { type: Boolean, default: false },
    configForm: { type: String, default: "" },
    sfData: { type: Object, default: () => {} }
  },

  components: {
    "ank-smart-form": () => {
      return AnkSmartForm;
    }
  },
  template: '<ank-smart-form ref="smartFormTest" :config="getConfig" :options="getOptions" />',

  beforeCreate() {},

  methods: {},

  computed: {
    getConfig: function() {
      return JSON.parse(this.configForm);
    },

    getOptions: function() {
      return {
        withCloseConfirmation: this.closeConfirmation
      };
    }
  }
});

export const BasicForm = Template.bind({});
BasicForm.parameters = {};
BasicForm.args = {
  closeConfirmation: false,
  configForm: JSON.stringify(
    {
      title: "Formulaire simple",
      type: "Demande de renseignement",
      structure: [
        {
          label: "Identification",
          name: "my_fr_ident",
          type: "frame",
          content: [
            {
              label: "Titre de la demande n°1",
              name: "my_title",
              type: "text"
            }
          ]
        }
      ]
    },
    null,
    2
  )
};
