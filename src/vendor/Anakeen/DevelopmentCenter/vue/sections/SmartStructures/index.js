import SmartStructures from "./SmartStructures.vue";
import SSContent from "./SSContent.vue";
import Infos from "./Infos/Infos.vue";
import Structure from "./Structure/Structure";
import ParameterValues from "./Parameters/ParametersFields.vue";
import DefaultsFields from "./Structure/DefaultValues.vue";
import DefaultsParamFields from "./Parameters/ParametersValues.vue";
import Parameters from "./Parameters/Parameters.vue";
import Fields from "./Structure/Fields.vue";

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
          name: "SmartStructures::fields",
          path: "fields",
          component: Fields,
          props: true, //Set ssName as a vue component prop
          children: [
            {
              name: "SmartStructures::fields::structure",
              path: "structure/",
              component: Structure,
              props: true
            },
            {
              name: "SmartStructures::fields::defaults",
              path: "defaults/",
              component: DefaultsFields,
              props: true
            }
          ]
        },
        {
          name: "SmartStructures::parameters",
          path: "parameters/",
          component: Parameters,
          props: true,
          children: [
            {
              name: "SmartStructures::parameters::parametersValues",
              path: "parameters/values/",
              component: ParameterValues,
              props: true
            },
            {
              name: "SmartStructures::parameters::defaultParamFields",
              path: "parameters/fields/",
              component: DefaultsParamFields,
              props: true
            }
          ]
        }
      ]
    }
  ]
};
