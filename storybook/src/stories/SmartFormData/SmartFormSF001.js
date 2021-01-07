import SmartFormTestData from "./lib/SmartFormTestData";
import { smartElementGetRawValue, smartElementGetRawValues } from "../../addons/AnkTestsManager/testGetValue.spec";

const SFData = new SmartFormTestData("SF001 : Formulaire texte");

SFData.formConfig = {
  structure: [
    {
      label: "Textes",
      name: "my_fr_ident",
      type: "frame",
      content: [
        {
          label: "Texte simple",
          name: "my_title",
          type: "text"
        },
        {
          label: "Plusieurs textes simples",
          name: "my_t_texts",
          type: "array",
          content: [
            {
              label: "Colonne de textes",
              name: "my_col_text",
              type: "text"
            }
          ]
        }
      ]
    }
  ]
};

SFData.readme = `
# Test SF001 :elephant:

## Présentation

Ce test affiche un formulaire composé d'un champ text mono-ligne et d'un tableau avec une seule colonne comportant un champ texte.

`;
SFData.userTests = [
  {
    testId: "SF001-UT01",
    description: 'Vérifier que le champ "`Texte simple`" est présent et modifiable'
  },
  {
    testId: "SF001-UT02",
    description: 'Vérifier que le cadre "`Textes`" est affiché'
  },
  {
    testId: "SF001-UT03",
    description: 'Vérifier que le tableau "`Plusieurs textes simples`" est vide (pas de rangée)'
  },
  {
    testId: "SF001-UT04",
    description: 'Vérifier que dans le tableau  le bouton "`+`" permet d\'ajouter des rangées dans le tableau'
  },
  {
    testId: "SF001-UT05",
    description: 'Vérifier que le tableau a une seule colonne nommée "*Colonne de textes*"'
  },
  {
    testId: "SF001-UT06",
    description: "Vérifier que chaque rangée du tableau est un champ texte mono-lignes modifiable"
  }
];

SFData.automaticTests = [
  {
    testId: "SF001-AT01",
    humanTasks: ['Remplir le champ "Texte simple" avec la valeur "Le jardin"'],
    title: 'Le champ "Texte simple" a la valeur "Le jardin"',

    testCallback: async function(arg) {
      return await smartElementGetRawValue({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_title",
      expected: "Le jardin"
    }
  },
  {
    testId: "SF001-AT02",
    humanTasks: [
      'Mettre 2 rangées dans le tableau "Plusieurs textes simples"',
      'Remplir la première rangée avec la valeur "Bonjour" et la deuxième avec "Tout le monde"'
    ],
    title: 'Le tableau "Plusieurs textes simples" a deux rangées "Bonjour" et "Tout le monde"',

    testCallback: async function(arg) {
      return await smartElementGetRawValues({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_col_text",
      expected: ["Bonjour", "Tout le monde"]
    }
  }
];
export default SFData;
