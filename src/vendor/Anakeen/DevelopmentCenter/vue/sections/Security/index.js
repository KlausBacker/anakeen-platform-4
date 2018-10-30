import Security from "./SecurityDevCenter";
import Routes from "../Routes/RoutesDevCenter";
import Roles from "../Role/RoleDevCenter";
import RoutesAcl from "../Routes/RoutesAcl/RoutesAcl";
import AclAccount from "../Routes/RoutesPermissions/RoutesPermissions";
import SmartStructures from "../SmartStructuresSecurity/SmartStructuresSecurity.vue";
import SmartStructuresContent from "../SmartStructuresSecurity/SmartStructuresSecurityContent.vue";
import SmartStructuresSections from "../SmartStructuresSecurity/subsections/export";

import SmartElements from "../SmartElementsSecurity/SmartElementsSecurity.vue";
export default {
  label: "Security",
  name: "Security",
  path: "security",
  order: 2,
  component: Security,
  children: [
    {
      name: "Security::Routes",
      path: "routes",
      component: Routes,
      children: [
        {
          name: "Security::Routes::RoutesAcl",
          path: "access/controls/",
          component: RoutesAcl
        },
        {
          name: "Security::Routes::RoutesPermissions",
          path: "access/permissions/",
          component: AclAccount
        }
      ]
    },
    {
      name: "Security::SmartStructures",
      path: "smartStructures",
      component: SmartStructures,
      children: [
        {
          name: "Security::SmartStructures::name",
          path: ":ssName",
          component: SmartStructuresContent,
          children: [
            {
              name: "Security::SmartStructures::Infos",
              path: "infos",
              component: SmartStructuresSections.Infos,
              props: true // Set ssName as a vue component prop
            },
            {
              name: "Security::SmartStructures::Structures",
              path: "structureProfile",
              component: SmartStructuresSections.Structures,
              props: true // Set ssName as a vue component prop
            },
            {
              name: "Security::SmartStructures::Elements",
              path: "elementsProfile",
              component: SmartStructuresSections.Elements,
              props: true // Set ssName as a vue component prop
            },
            {
              name: "Security::SmartStructures::Fields",
              path: "fields",
              component: SmartStructuresSections.Fields,
              props: true // Set ssName as a vue component prop
            }
          ]
        }
      ]
    },
    {
      name: "Security::SmartElements",
      path: "smartElements",
      component: SmartElements
    },
    {
      name: "Security::Roles",
      path: "roles",
      component: Roles
    }
  ]
};
