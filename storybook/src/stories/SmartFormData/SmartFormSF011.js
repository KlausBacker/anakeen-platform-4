import Chai from "chai";
import { smartElementGetRawValue, smartElementGetRawValues } from "../../addons/AnkTestsManager/testGetValue.spec";
import SmartFormTestBeforeRender from "./lib/SmartFormTestBeforeRender";

const expect = Chai.expect;

class SFTest011 extends SmartFormTestBeforeRender {}

const SF1 = new SFTest011("SF011 : Test des événements de rendus numérique");
SF1.readme = `
# Test SF011 :dolphin:

## Test automatique pour les événements numériques

Tests des valeurs prérenseignées dans le formulaire.
Le formulaire contient 3 champs numériques et un tableau de 3 colonnes.
`;
SF1.formConfig = {
  structure: [
    {
      label: "Formulaire de nombres",
      name: "my_fr_ident",
      type: "frame",
      content: [
        {
          label: "Nombre entier",
          name: "my_int",
          type: "int"
        },
        {
          label: "Nombre décimal",
          name: "my_double",
          type: "double"
        },
        {
          label: "Produit intérieur brut",
          name: "my_money",
          type: "money"
        },
        {
          label: "Des nombres par dizaines",
          name: "my_t_numbers",
          type: "array",
          content: [
            {
              label: "Quelques nombres",
              name: "my_col_int",
              type: "text"
            },
            {
              label: "Précision",
              name: "my_col_double",
              type: "double"
            },
            {
              label: "Des sous",
              name: "my_col_money",
              type: "money"
            }
          ]
        }
      ]
    }
  ],
  values: {
    my_int: 23678,
    my_double: 2.71828,
    my_money: 2425708000000,
    my_col_int: [6, 28, 496, 8128],
    my_col_double: [3.1415926535, 0.412454033, 0.5772156649, 891763],
    my_col_money: [65.2, 67789.5, -45638, -300]
  }
};

SF1.automaticTests = [
  // ------------------- SF011-AT01-----------
  {
    testId: "SF011-AT01",
    title: "Valeur du champ `Nombre entier`",

    testCallback: async function(arg) {
      return await smartElementGetRawValue({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_int",
      expected: SF1.formConfig.values.my_int
    }
  },
  // ------------------- SF011-AT02 -----------
  {
    testId: "SF011-AT02",
    title: "Valeur du champ `Nombre décimal`",

    testCallback: async function(arg) {
      return await smartElementGetRawValue({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_double",
      expected: SF1.formConfig.values.my_double
    }
  },
  // ------------------- SF011-AT03 -----------
  {
    testId: "SF011-AT03",
    title: "Valeur du champ `Produit intérieur brut`",

    testCallback: async function(arg) {
      return await smartElementGetRawValue({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_money",
      expected: SF1.formConfig.values.my_money
    }
  },

  // ------------------- SF011-AT04 -----------
  {
    testId: "SF011-AT04",
    title: "Valeur des champs `Quelques nombres`",

    testCallback: async function(arg) {
      return await smartElementGetRawValues({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_col_int",
      expected: SF1.formConfig.values.my_col_int
    }
  },
  // ------------------- SF011-AT05 -----------
  {
    testId: "SF011-AT05",
    title: "Valeur des champs `Précision`",

    testCallback: async function(arg) {
      return await smartElementGetRawValues({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_col_double",
      expected: SF1.formConfig.values.my_col_double
    }
  },

  // ------------------- SF011-AT06 -----------
  {
    testId: "SF011-AT06",
    title: "Valeur des champs `Des sous`",

    testCallback: async function(arg) {
      return await smartElementGetRawValues({
        controller: this.smartController,
        fieldId: arg.fieldId,
        expected: arg.expected
      });
    },
    testCallbackArgs: {
      fieldId: "my_col_money",
      expected: SF1.formConfig.values.my_col_money
    }
  },

  // ------------------- "SF011-AT10 -----------
  {
    testId: "SF011-AT10",

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
        "my_int",
        "my_double",
        "my_money",
        "my_t_numbers",
        "my_col_int",
        "my_col_double",
        "my_col_money",
        "my_col_int",
        "my_col_double",
        "my_col_money",
        "my_col_int",
        "my_col_double",
        "my_col_money",
        "my_col_int",
        "my_col_double",
        "my_col_money"
      ]
    }
  }
];
export default SF1;
