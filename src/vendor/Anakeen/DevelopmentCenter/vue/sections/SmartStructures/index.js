import SmartStructures from "./SmartStructures.vue";
import SSContent from "./SSContent.vue";
import Infos from "./Infos.vue";
import Structure from "./Structure/Structure";
import Others from "./Others.vue";

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
          name: "SmartStructures::others",
          path: "others",
          component: Others,
          props: true // Set ssName as a vue component prop
        }
      ]
    }
  ]
};
