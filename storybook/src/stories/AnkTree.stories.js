import Vue from "vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";

import GroupTreeNgVue from "../../../user-interfaces/components/src/AnkTree/AnkTree.vue";

Vue.use(setup);

export default {
  title: "Ui Component/Ank Tree",
  component: GroupTreeNgVue,
  argTypes: {
    treeUrl: {
      control: { type: "text" },
      table: { disable: true },
      description: "Url Données de l'arbre"
    },
    expanded: {
      control: { type: "boolean" },
      description: "Tout déplier"
    },
    filter: {
      control: { type: "text" },
      description: "Filtre"
    },
    displayItemCount: {
      control: { type: "boolean" },
      description: "Affichage des compteurs utilisateurs"
    },
    displayChildrenCount: {
      control: { type: "boolean" },
      description: "Affichage des compteurs groupes"
    },
    displaySelectedParent: {
      control: { type: "boolean" },
      description: "Mise en évidence des groupes parents lors de la sélection"
    },
    displayFilter: {
      control: { type: "boolean" },
      description: "Affichage du filtre utilisé"
    },
    itemHeight: {
      control: { type: "number" },
      description: "Hauteur d'une ligne (en rem)"
    },
    levelIndentationWidth: {
      control: { type: "number" },
      description: "largeur de l'indentation (en rem)"
    },
    scrollDebounce: {
      control: { type: "number" },
      description: "scroll timeout (en ms)"
    },
    customTranslations: {
      control: { type: "object" },
      description: "Différents textes à traduire"
    },
    multipleSelection: {
      control: { type: "boolean" },
      description: "Ajout des boites à cocher"
    }
  }
};

const Template = (args, { argTypes }) => ({
  props: Object.keys(argTypes),

  components: {
    "group-tree": () => {
      return Vue.$_globalI18n.recordCatalog().then(() => {
        return GroupTreeNgVue;
      });
    }
  },
  template:
    '<group-tree style="flex-basis:300px; padding: 1rem;border:solid 2px darkgreen"  v-bind="$props"  @onSelectItem="storySelect"/>',

  methods: {
    storySelect(event, data) {
      // eslint-disable-next-line no-console
      console.log("SELECT on story ", data);
    }
  }
});

export const IgroupTree = Template.bind({});
IgroupTree.args = {
  treeUrl: "/api/v2/admin/account/grouptree/",
  expanded: false,
  displayItemCount: true,
  displayChildrenCount: true,
  displaySelectedParent: true,
  itemHeight: 2,
  customTranslations: { headerLabel: "Test libellé", groupCount2: "Sous-groupes spéciaux" }
};
