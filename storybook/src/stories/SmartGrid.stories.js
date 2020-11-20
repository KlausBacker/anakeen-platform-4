import Vue from "vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";
import AnkSmartElementGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";

//import AnkSmartElementGrid from "../../../user-interfaces/components/src/AnkSmartElementGrid";
import AnkSmartElementGridVue from "../../../user-interfaces/components/src/AnkSEGrid/AnkSEGrid.vue";

import { action } from "@storybook/addon-actions";

import "@anakeen/user-interfaces/components/scss/SmartElementGrid.scss";
import "./SmartGrid.stories.scss";
Vue.use(setup);

export default {
  title: "Ui Component/Smart Grid",
  component: AnkSmartElementGridVue,
  argTypes: {
    actions: { default: [] },
    filterable: { default: true, control: { type: "boolean" } },
    columns: { default: [{ field: "title", property: true }] },
    collection: { default: "" },
    sortable: { default: true, control: { type: "boolean" } },
    pageable: {
      control: { type: "object" },
      default: {
        buttonCount: 0,
        showCurrentPage: false,
        info: true,
        pageSize: 10,
        pageSizes: [10, 20, 50]
      }
    },
    // Props which are not under control
    smartCriteriaValue: { default: [], control: { disable: true } },
    sort: { control: { disable: true } },
    filter: { control: { disable: true } },
    subHeader: { control: { disable: true } },
    controller: { control: { disable: true } },
    actionColumnTitle: { control: { disable: true } },
    persistStateKey: { control: { disable: true } },
    contextTitlesSeparator: { control: { disable: true } },
    emptyCellText: { control: { disable: true } },
    inexistentCellText: { control: { disable: true } },
    selectable: { control: { disable: true } },
    maxRowHeight: { control: { disable: true } },
    configUrl: { control: { disable: true } },
    exportUrl: { control: { disable: true } },
    selectedField: { control: { disable: true } },
    customData: { control: { disable: true } },
    contentUrl: { control: { disable: true } },
    actionField: { control: { disable: true } }
  }
};

/**
 * Get only boolean and number properties under control
 * Get also properties when default is set into argType items
 * @param argTypes
 * @returns {{}}
 */
const getPropsFromArgType = argTypes => {
  let props = {};
  for (const [key, value] of Object.entries(argTypes)) {
    if (
      value.table &&
      value.table.category === "props" &&
      value.table.disable !== true &&
      value.control.disable !== true
    ) {
      if (value.default !== undefined) {
        props[key] = { default: value.default };
      } else if (value.control.type === "boolean" || value.control.type === "number") {
        props[key] = {};
      }
    }
  }
  return props;
};

const Template = (args, { argTypes }) => {
  return {
    props: getPropsFromArgType(argTypes),

    components: {
      "smart-element-grid": () => {
        return Vue.$_globalI18n.recordCatalog().then(() => {
          return AnkSmartElementGrid;
        });
      }
    },
    template: '<smart-element-grid v-on="listeners" :columns="columns" :collection="collection" v-bind="$props" />',
    computed: {
      listeners: function() {
        const listenOn = {};
        for (const [key, value] of Object.entries(argTypes)) {
          if (value.table && value.table.category === "events") {
            listenOn[key] = (/*e, ...o*/) => {
              action(key)();
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
  };
};

export const SimpleGrid = Template.bind({});
SimpleGrid.storyName = "Grille de profils";
SimpleGrid.args = {
  collection: "PDOC",
  sortable: true,
  actions: [],
  pageable: {
    buttonCount: 0,
    showCurrentPage: false,
    info: true,
    pageSize: 20,
    pageSizes: [20, 50]
  },
  filterable: true,
  columns: [
    { field: "initid", property: true, width: 100 },
    { field: "title", property: true },
    { field: "mdate", property: true }
  ]
};

export const BillGrid = Template.bind({});
BillGrid.storyName = "Grille de facture";
BillGrid.args = {
  collection: "DEVBILL",
  sortable: true,
  actions: [],
  pageable: {
    buttonCount: 0,
    showCurrentPage: false,
    info: true,
    pageSize: 10,
    pageSizes: [10, 20, 50]
  },
  filterable: true,
  columns: [
    { field: "initid", property: true, width: 100 },
    { field: "title", property: true },
    { field: "bill_author", title: "Auteur" },
    { field: "bill_location", title: "Ville" },
    { field: "bill_cost" }
  ]
};

export const ActionGrid = Template.bind({});
ActionGrid.storyName = "Avec boutons";
ActionGrid.args = {
  collection: "PDOC",
  sortable: true,
  actions: [
    {
      action: "display",
      title: "Display"
    },
    {
      action: "modify",
      title: "Modify",
      iconClass: "fa fa-edit"
    },
    {
      action: "delete",
      title: "Delete",
      iconClass: "fa fa-trash"
    }
  ],
  pageable: {
    buttonCount: 0,
    showCurrentPage: false,
    info: true,
    pageSize: 20,
    pageSizes: [20, 50]
  },
  filterable: true,
  columns: [
    { field: "title", property: true },
    { field: "fromid", property: true },
    { field: "mdate", property: true }
  ]
};
