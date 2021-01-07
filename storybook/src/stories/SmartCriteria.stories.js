import Vue from "vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import AnkSmartCriteria from "@anakeen/user-interfaces/components/lib/AnkSmartCriteria.esm";

import AnkSmartCriteriaVue from "../../../user-interfaces/components/src/AnkSmartCriteria/AnkSmartCriteria.vue";

import { action } from "@storybook/addon-actions";

import "@anakeen/user-interfaces/components/scss/AnkSmartElement.scss";
// import "../../../user-interfaces/components/scss/bootstrap.scss";
// import "../../../user-interfaces/components/scss/kendo.scss";
// import "../../../user-interfaces/components/scss/AnkSmartElement.scss";

Vue.use(setup);

export default {
  title: "Ui Component/Smart Criteria",
  component: AnkSmartCriteriaVue,
  argTypes: {
    configCriteria: { description: "Configuration du <b>Smart Criteria</b>", control: { type: "text" } },
    config: { description: "Configuration du <b>Smart Criteria</b>", control: { type: "object" } }
  }
};

// eslint-disable-next-line no-unused-vars
const Template = (args, { argTypes }) => ({
  props: {
    submit: { type: Boolean, default: false },
    responsiveColumns: { type: Array, default: [] },
    configCriteria: { type: String, default: "" }
  },

  components: {
    "ank-smart-criteria": () => {
      return Vue.$_globalI18n.recordCatalog().then(() => {
        return AnkSmartCriteria;
      });
    }
  },
  template:
    '<ank-smart-criteria  :config="getConfig"  :submit="submit"  :responsiveColumns="responsiveColumns" v-on="listeners"/>',
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

export const BasicCriteria = Template.bind({});
BasicCriteria.args = {
  closeConfirmation: false,

  configCriteria: JSON.stringify(
    {
      title: "Filtre des factures",
      defaultStructure: "DEVBILL",
      criterias: [
        {
          kind: "property",
          field: "title",
          label: "Titre de facture"
        },
        {
          kind: "field",
          field: "bill_clients",
          default: {
            operator: {
              key: "oneEqualsMulti",
              option: ["all"]
            }
          }
        },
        {
          kind: "field",
          field: "bill_billdate",
          label: "Date de facturation",
          default: {
            value: ["1970-01-01", "2020-01-01"]
          }
        },
        {
          kind: "field",
          field: "bill_cost",
          label: "Coût"
        }
      ]
    },
    null,
    2
  )
};

export const SubmitCriteria = Template.bind({});
SubmitCriteria.args = {
  submit: true,
  configCriteria: JSON.stringify(
    {
      title: "Recherche des factures",
      submit: true,
      defaultStructure: "DEVBILL",
      criterias: [
        {
          kind: "property",
          field: "title",
          label: "Titre de facture"
        },
        {
          kind: "field",
          field: "bill_clients",
          default: {
            operator: {
              key: "oneEqualsMulti",
              option: ["all"]
            }
          }
        }
      ]
    },
    null,
    2
  )
};
export const ResponsiveCriteria = Template.bind({});
ResponsiveCriteria.args = {
  responsiveColumns: [
    {
      number: 2,
      minWidth: "30rem",
      maxWidth: null,
      grow: true
    }
  ],
  configCriteria: JSON.stringify(
    {
      title: "Recherche des factures",
      submit: true,
      defaultStructure: "DEVBILL",
      criterias: [
        {
          kind: "property",
          field: "title",
          label: "Titre de facture"
        },
        {
          kind: "field",
          field: "bill_clients",
          default: {
            operator: {
              key: "oneEqualsMulti",
              option: ["all"]
            }
          }
        }
      ]
    },
    null,
    2
  )
};
