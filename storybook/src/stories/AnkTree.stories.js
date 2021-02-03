import Vue from "vue";
import setup from "@anakeen/user-interfaces/components/lib/setup.esm";

import GroupTreeNgVue from "../../../user-interfaces/components/src/AnkTree/AnkTree.vue";
import { action } from "@storybook/addon-actions";

Vue.use(setup);

export default {
  title: "Ui Component/Ank Tree",
  component: GroupTreeNgVue,
  argTypes: {
    treeUrl: {
      control: { type: "text" },
      description: "Url Données de l'arbre"
    },
    treeOpenNodeUrl: {
      control: { type: "text" },
      description: "Url pour compléter les données d'une branche"
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
      action("onSelectItem")(event, data);
    }
  }
});

export const IgroupTree = Template.bind({});
IgroupTree.storyName = "Tous les groupes";
IgroupTree.args = {
  treeUrl: "/api/v2/admin/account/grouptree/",
  treeOpenNodeUrl: "/api/v2/admin/account/grouptree/{nodeId}",
  displayItemCount: true,
  displayChildrenCount: true,
  displaySelectedParent: true,
  itemHeight: 2,
  customTranslations: { headerLabel: "Test libellé", groupCount: "Sous-groupes spéciaux" }
};

export const IgroupExpandTree = Template.bind({});
IgroupExpandTree.storyName = "Tous les groupes dépliés";
IgroupExpandTree.args = {
  treeUrl: "/api/v2/admin/account/grouptree/all",
  treeOpenNodeUrl: "/api/v2/admin/account/grouptree/{nodeId}",
  displayItemCount: true,
  displayChildrenCount: true,
  displaySelectedParent: true,
  itemHeight: 2,
  customTranslations: { headerLabel: "Test libellé", groupCount: "Sous-groupes spéciaux" }
};

export const IgroupNotAuto = Template.bind({});
IgroupNotAuto.storyName = "Les groupes administrables";
IgroupNotAuto.args = {
  treeUrl: "/api/v2/admin/account/grouptree/nocategory/",
  treeOpenNodeUrl: "/api/v2/admin/account/grouptree/nocategory/{nodeId}",
  displayItemCount: true,
  displayChildrenCount: true,
  displaySelectedParent: true,
  itemHeight: 2,
  customTranslations: { headerLabel: "Groupes non catégorisés" }
};

export const IgroupExpandedNotAuto = Template.bind({});
IgroupExpandedNotAuto.storyName = "Les groupes administrables dépliés";
IgroupExpandedNotAuto.args = {
  treeUrl: "/api/v2/admin/account/grouptree/nocategory/all",
  treeOpenNodeUrl: "/api/v2/admin/account/grouptree/nocategory/{nodeId}",
  displayItemCount: true,
  displayChildrenCount: true,
  displaySelectedParent: true,
  itemHeight: 2,
  customTranslations: { headerLabel: "Groupes non catégorisés" }
};
