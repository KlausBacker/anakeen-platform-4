import SmartStructures from "./SmartStructures.vue";
import SSContent from "./SSContent.vue";
import Infos from "./Infos.vue";
import Structure from "./Structure/Structure";
import Others from "./Others.vue";
import Parameters from "./Parameters/Parameters.vue";
import Defaults from "./Defaults/Defaults.vue";

export default {
  name: "SmartStructures",
  path: "smartStructures",
  label: "Smart Structures",
  order: 1, // Set smart structure in first position
  component: SmartStructures,
  children: [
    {
      name: "SmartStructures::name",
      path: ":ssName",
      component: SSContent,
      children: [
        {
          name: "SmartStructures::infos",
          path: "infos",
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
          props: true
        },
        {
          name: "SmartStructures::defaults",
          path: "defaults",
          component: Defaults,
          props: true
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
