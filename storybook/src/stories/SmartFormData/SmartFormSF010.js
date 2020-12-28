import Chai from "chai";
import ChaiDom from "chai-dom";
import { smartElementGetRawValue, smartElementGetRawValues } from "../../addons/AnkTestsManager/testGetValue.spec";
import SmartFormTestBeforeRender from "./lib/SmartFormTestBeforeRender";

Chai.use(ChaiDom);
const expect = Chai.expect;

// const assert = Chai.assert;

class SFTest010 extends SmartFormTestBeforeRender {}

const SF1 = new SFTest010("SF010 : Test des événements de rendus de texte");
SF1.readme = `
# Test SF010 :crocodile:

## Test automatique pour les événements 

Tests des valeurs prérenseignées dans le formulaire.
Le formulaire contient 2 champs textes et un tableau de 2 colonnes.
`;
SF1.formConfig = {
  structure: [
    {
      label: "Formulaire de textes",
      name: "my_fr_ident",
      type: "frame",
      content: [
        {
          label: "Sujet",
          name: "my_text",
          type: "text"
        },
        {
          label: "Description",
          name: "my_longtext",
          type: "longtext"
        },
        {
          label: "Articles",
          name: "my_t_longtexts",
          type: "array",
          content: [
            {
              label: "Sujet de l'article",
              name: "my_col_simpletext",
              type: "text"
            },
            {
              label: "Description détaillée",
              name: "my_col_longtext",
              type: "longtext"
            }
          ]
        }
      ]
    }
  ],
  values: {
    my_longtext:
      "Les Manidae (ou Manidés) sont une famille de mammifères pholidotes (les pangolins) regroupant tous les pangolins modernes avec toutes les espèces actuelles. Les pangolins actuels (du malais)",
    my_text: "Le pangolin",
    my_col_simpletext: ["Ursidés, Ours", "Panthera leo"],
    my_col_longtext: [
      "Les ours modernes ont comme caractéristiques un corps grand, trapu et massif, un long museau, un pelage dense, des pattes plantigrades à cinq griffes non rétractiles et une queue courte. ",
      "Le Lion (Panthera leo) est une espèce de mammifères carnivores de la famille des Félidés. La femelle du lion est la lionne, son petit est le lionceau. Le mâle adulte, aisément reconnaissable à son importante crinière, accuse une masse moyenne qui peut être variable selon les zones géographiques où il se trouve, allant de 180 kg pour les lions de Kruger à 230 kg pour les lions de Transvaal. Certains spécimens très rares peuvent dépasser exceptionnellement 250 kg. "
    ]
  }
};

SF1.automaticTests = [
  // ------------------- SF010-AT01-----------
  {
    testId: "SF010-AT01",
    title: "Valeur du champ `Sujet`",

    testCallback: async function(arg) {
      return await smartElementGetRawValue({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_text",
      expected: SF1.formConfig.values.my_text
    }
  },
  // ------------------- SF010-AT02 -----------
  {
    testId: "SF010-AT02",
    title: "Valeur du champ `Description`",

    testCallback: async function(arg) {
      return await smartElementGetRawValue({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_longtext",
      expected: SF1.formConfig.values.my_longtext
    }
  },

  // ------------------- SF010-AT03 -----------
  {
    testId: "SF010-AT03",
    title: "Valeur des champs `Sujet de l'article`",

    testCallback: async function(arg) {
      return await smartElementGetRawValues({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_col_simpletext",
      expected: SF1.formConfig.values.my_col_simpletext
    }
  },

  // ------------------- SF010-AT04 -----------
  {
    testId: "SF010-AT04",
    title: "Valeur des champs `Description détaillée`",

    testCallback: async function(arg) {
      return await smartElementGetRawValues({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_col_longtext",
      expected: SF1.formConfig.values.my_col_longtext
    }
  },
  // ------------------- SF010-AT10 -----------
  {
    testId: "SF010-AT10",

    title: "Vérification de l'ordre des affichages des champs",

    testCallback: async function(arg) {
      expect(
        this.renderedFields,
        `L'ordre n'est pas respecté : \nAttendu : "${arg.expected.toString()}"\nObtenu : "${this.renderedFields.toString()}"\n`
      ).to.eql(arg.expected);

      return `L'ordre obtenu est "${this.renderedFields.toString()}"`;
    },
    testCallbackArgs: {
      expected: [
        "my_fr_ident",
        "my_text",
        "my_longtext",
        "my_t_longtexts",
        "my_col_simpletext",
        "my_col_longtext",
        "my_col_simpletext",
        "my_col_longtext"
      ]
    }
  },
  // ------------------- SF010-AT11 -----------
  {
    testId: "SF010-AT11",

    title: "Vérification de l'affichage dans la DOM",

    testCallback: async function(arg) {
      arg.fields.forEach(fieldid => {
        const selector = `.dcpAttribute__content[data-attrid="${fieldid}"]`;

        expect(document.querySelector(selector) || {}, selector).to.not.be.empty;
        expect(document.querySelector(selector) || {}, `DOM élément ".dcpAttribute[name="${fieldid}"]" non trouvé`).to
          .be.visible;
      });
      arg.fieldSets.forEach(fieldid => {
        const selector = `.dcpFrame[data-attrid="${fieldid}"]`;

        expect(document.querySelector(selector) || {}, selector).to.not.be.empty;
        expect(document.querySelector(selector) || {}, `DOM élément ".dcpAttribute[name="${fieldid}"]" non trouvé`).to
          .be.visible;
      });
      arg.fieldArrays.forEach(fieldid => {
        const selector = `.dcpArray[data-attrid="${fieldid}"]`;

        expect(document.querySelector(selector) || {}, selector).to.not.be.empty;
        expect(document.querySelector(selector) || {}, `DOM élément ".dcpAttribute[name="${fieldid}"]" non trouvé`).to
          .be.visible;
      });
      return `Les champs  "${arg.fields.toString()}" sont affichés`;
    },
    testCallbackArgs: {
      fields: [
        "my_text",
        "my_longtext",

        "my_col_simpletext",
        "my_col_longtext",
        "my_col_simpletext",
        "my_col_longtext"
      ],

      fieldSets: ["my_fr_ident"],
      fieldArrays: ["my_t_longtexts"]
    }
  }
];
export default SF1;
