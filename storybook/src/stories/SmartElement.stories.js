import Vue from "vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";

//import AnkSmartElement from "../../../user-interfaces/components/src/AnkSmartElement";
import AnkSmartElementVue from "../../../user-interfaces/components/src/AnkSmartElement/AnkSmartElement.vue";

import "@anakeen/user-interfaces/components/scss/AnkSmartElement.scss";

import { action } from "@storybook/addon-actions";

//import SEReadme from "./SmartElement.md";
import DocRacine from "./Racine.md";

Vue.use(setup);

export default {
  title: "Ui Component/Smart Element",
  component: AnkSmartElementVue,
  argTypes: {
    initid: { control: "text", description: "Identifiant du smart Element" },
    viewId: {
      control: { type: "inline-radio", options: ["!defaultConsultation", "!defaultEdition"] },
      description: "Identifiant de la vue du Smart Element"
    },
    beforeRender: {
      name: "beforeRender",
      type: { name: "void" },
      table: { category: "events" },
      description: "Événement déclenché avant l'éxécution de la fonction de la génération de la DOM"
    }
  },
  parameters: {
    controls: { expanded: false, hideNoControlsWarning: true }
  }
};

const Template = (args, { argTypes }) => ({
  props: ["initid", "viewId", "revision"],

  components: {
    "ank-smart-element": () => {
      return AnkSmartElement;
    }
  },

  template: '<ank-smart-element   v-on="listeners" v-bind="$props" />',
  computed: {
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
  },
  methods: {
    // @FIXME: need to add this workaround to se event in action addOns
    // I don't know why but it test if Vue has toJSON method
    toJSON: function() {
      //JSON.stringify;
      return "boo";
    }
  }
});

export const RacineElement = Template.bind({});
RacineElement.parameters = {
  readme: {
    sidebar: DocRacine
  }
};
RacineElement.storyName = "Racine";
RacineElement.args = {
  initid: 9,
  viewId: "!defaultConsultation",
  revision: -1
};

export const ToutElement = Template.bind({});
ToutElement.parameters = {
  readme: {
    sidebar: DocRacine
  }
};
ToutElement.storyName = "Tout type";
ToutElement.args = {
  initid: "TST_ALL_COMPLET",
  viewId: "!defaultConsultation",
  revision: -1
};

export const CouleurCss = Template.bind({});
CouleurCss.parameters = {
  readme: {
    sidebar: DocRacine
  }
};
CouleurCss.storyName = "Couleur";
CouleurCss.args = {
  initid: "TST_ALL_COMPLET",
  viewId: "CSSCOLOR",
  revision: -1
};
