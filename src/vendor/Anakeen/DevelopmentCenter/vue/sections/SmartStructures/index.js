import SmartStructures from "./SmartStructures.vue";
import SSContent from "./SSContent.vue";
import Infos from "./Infos.vue";
import Structure from "./Structure/Structure";
import Others from "./Others.vue";
import Parameters from "./Parameters/Parameters.vue";
import ParameterFields from "./Parameters/Fields/ParameterFields.vue";
import ParameterValues from "./Parameters/Values/ParameterValues.vue";
import DefaultsFields from "./Defaults/Fields/DefaultsFields.vue";
import DefaultsValues from "./Defaults/Values/DefaultsValues.vue";
import DefaultsParamFields from "./Defaults/ParameterFields/DefaultsParamFields.vue";
import Defaults from "./Defaults/Defaults.vue";

export default {
  name: "SmartStructures",
  path: "smartStructures",
  meta: {
    label: "Smart Structures"
  },
  order: 1, // Set smart structure in first position
  component: SmartStructures,
  children: [
    {
      name: "SmartStructures::name",
      path: ":ssName",
      component: SSContent,
      meta: {
        label: ":ssName"
      },
      children: [
        {
          name: "SmartStructures::infos",
          path: "infos",
          meta: {
            label: "Infos"
          },
          component: Infos,
          props: true // Set ssName as a vue component prop
        },
        {
          name: "SmartStructures::structure",
          path: "structure",
          component: Structure,
          props: true //Set ssName as a vue component prop
        },
        {
          name: "SmartStructures::parameters",
          path: "parameters",
          component: Parameters,
          props: true,
          children: [
            {
              name: "SmartStructures::parameters::parameterFields",
              path: "fields/",
              component: ParameterFields,
              props: true
            },
            {
              name: "SmartStructures::parameters::parameterValues",
              path: "values/",
              component: ParameterValues,
              props: true
            }
          ]
        },
        {
          name: "SmartStructures::defaults",
          path: "defaults",
          component: Defaults,
          props: true,
          children: [
            {
              name: "SmartStructures::defaults::defaultFields",
              path: "fields/",
              component: DefaultsFields,
              props: true
            },
            {
              name: "SmartStructures::defaults::defaultValues",
              path: "values/",
              component: DefaultsValues,
              props: true
            },
            {
              name: "SmartStructures::defaults::defaultParamFields",
              path: "parameters/fields/",
              component: DefaultsParamFields,
              props: true
            }
          ]
        },
        {
          name: "SmartStructures::others",
          path: "others",
          component: Others,
          props: true // Set ssName as a vue component prop
        }
      ]
    }
  ]
};
