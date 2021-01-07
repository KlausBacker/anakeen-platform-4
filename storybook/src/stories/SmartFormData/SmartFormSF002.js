import SmartFormTestData from "./lib/SmartFormTestData";
import { smartElementGetRawValue, smartElementGetRawValues } from "../../addons/AnkTestsManager/testGetValue.spec";

const SFData = new SmartFormTestData("SF002 : Formulaire texte multi-lignes");

SFData.formConfig = {
  structure: [
    {
      label: "Textes multi-lignes",
      name: "my_fr_ident",
      type: "frame",
      content: [
        {
          label: "Texte long",
          name: "my_longtext",
          type: "longtext"
        },
        {
          label: "Plusieurs textes longs",
          name: "my_t_longtexts",
          type: "array",
          content: [
            {
              label: "Colonne de textes longs",
              name: "my_col_longtext",
              type: "longtext"
            }
          ]
        }
      ]
    }
  ]
};

SFData.readme = `
# Test SF002 :balloon:

## Présentation

Ce test affiche un formulaire composé d'un champ text multi-lignes et d'un tableau avec une seule colonne comportant un champ texte.

`;

SFData.userTests = [
  {
    testId: "SF002-UT01",
    description: 'Vérifier que le champ "`Texte long`" est présent et modifiable'
  },
  {
    testId: "SF002-UT02",
    description:
      'Vérifier que le champ "`Texte long`" peut être redimensionné en hauteur.' +
      "  \nUn emblème en bas à droit des champs de saisie permet de retailler la zone de saisie."
  },
  {
    testId: "SF002-UT03",
    description: 'Vérifier que le tableau "Plusieurs textes longs" est vide'
  },
  {
    testId: "SF002-UT04",
    description: 'Vérifier que dans le tableau  le bouton "`+`" permet d\'ajouter des rangées dans le tableau'
  },
  {
    testId: "SF002-UT05",
    description: 'Vérifier que le tableau a une seule colonne nommée "*Colonne de textes longs*"'
  },
  {
    testId: "SF002-UT06",
    description: "Vérifier que chaque rangée du tableau est un champ texte multi-lignes modifiable"
  }
];
SFData.automaticTests = [
  {
    testId: "SF002-AT01",
    humanTasks: ['Remplir le champ `Texte long` avec les deux lignes "_Le jardin_" et "_est vert_"'],
    title: 'Le champ "`Texte long`" a la valeur "Le jardin\nest vert"',
    testCallback: async function(arg) {
      await smartElementGetRawValue({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_longtext",
      expected: "Le jardin\nest vert"
    }
  },
  {
    testId: "SF002-AT02",
    humanTasks: [
      'Mettre 2 rangées dans le tableau "`Plusieurs textes longs`"',
      'Remplir la première rangée avec la valeur "Bonjour" et la deuxième avec "Tout le monde"'
    ],
    title: 'Le tableau "`Plusieurs textes longs`" a deux rangées  \n"Bonjour" et "Tout le monde"',
    testCallback: async function(arg) {
      await smartElementGetRawValues({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_col_longtext",
      expected: ["Bonjour", "Tout le monde"]
    }
  }
];
export default SFData;
