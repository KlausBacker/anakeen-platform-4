import Vue from "vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import AnkSmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";

//import AnkSmartElement from "../../../user-interfaces/components/src/AnkSmartElement";
import AnkSmartElementVue from "../../../user-interfaces/components/src/AnkSmartElement/AnkSmartElement.vue";

import "@anakeen/user-interfaces/components/scss/AnkSmartElement.scss";

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

const Template = (/*args, { argTypes }*/) => ({
  props: ["initid", "viewId", "revision"],

  components: {
    "ank-smart-element": () => {
      return AnkSmartElement;
    }
  },

  template: '<ank-smart-element v-bind="$props" />',
  computed: {},
  methods: {}
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
