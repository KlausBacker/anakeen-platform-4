import Vue from "vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm.esm";

//import AnkSmartForm from "../../../user-interfaces/components/src/AnkSmartForm";
import AnkSmartFormVue from "../../../user-interfaces/components/src/AnkSmartForm/AnkSmartForm.vue";

import { action } from "@storybook/addon-actions";

import "@anakeen/user-interfaces/components/scss/AnkSmartElement.scss";
// import "../../../user-interfaces/components/scss/bootstrap.scss";
// import "../../../user-interfaces/components/scss/kendo.scss";
// import "../../../user-interfaces/components/scss/AnkSmartElement.scss";

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

// eslint-disable-next-line no-unused-vars
const Template = (args, { argTypes }) => ({
  props: {
    closeConfirmation: { type: Boolean, default: false },
    configForm: { type: String, default: "" }
  },

  components: {
    "ank-smart-form": () => {
      return AnkSmartForm;
    }
  },
  template:
    '<ank-smart-form ref="smartFormTest" @smartElementLoaded="onLoaded"  :config="getConfig" :options="getOptions"  v-on="listeners"/>',
  methods: {
    // @FIXME: need to add this workaround to se event in action addOns
    // I don't know why but it test if Vue has toJSON method
    toJSON: function() {
      //JSON.stringify;
      return "boo";
    },
    onLoaded() {
      const controller = this.$refs.smartFormTest.smartElementWidget;
      controller._tested_ = args.testId;
    }
  },

  computed: {
    getConfig: function() {
      return JSON.parse(this.configForm);
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
          listenOn[key] = function(ev) {
            action(key)(ev);
          };
        }
      }

      return listenOn;
    }
  }
});

export const BasicForm = Template.bind({});
BasicForm.parameters = {
  AnkTests: [
    {
      title: "Test getValue",
      jest: "testGetValue",
      fieldId: "my_title",
      expected: "toto"
    }
  ]
};
BasicForm.args = {
  closeConfirmation: false,
  testId: "testGetValue",
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

import SF001Data from "./SmartFormData/SmartFormSF001.json";
import SF001Readme from "./SmartFormData/SmartFormSF001.md";
export const TestSF001 = Template.bind({});
TestSF001.parameters = {
  readme: {
    sidebar: SF001Readme
  },
  AnkTests: [
    {
      title: "Test login",
      jest: "testLogin"
    },
    {
      title: "Test 2",
      jest: "test2"
    },
    {
      title: "Test 5",
      jest: "test5"
    }
  ]
};
TestSF001.storyName = SF001Data.title;
TestSF001.args = {
  closeConfirmation: false,
  configForm: JSON.stringify(SF001Data, null, 2)
};

import SF002Data from "./SmartFormData/SmartFormSF002.json";
import SF002Readme from "./SmartFormData/SmartFormSF002.md";
export const TestSF002 = Template.bind({});
TestSF002.parameters = {
  readme: {
    sidebar: SF002Readme
  }
};
TestSF002.storyName = SF002Data.title;
TestSF002.args = {
  closeConfirmation: false,
  configForm: JSON.stringify(SF002Data, null, 2)
};
