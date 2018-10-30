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
      meta: {
        label: "Routes"
      },
      component: Routes,
      children: [
        {
          name: "Security::Routes::RoutesAcl",
          path: "access/controls/",
          meta: {
            label: "Acls"
          },
          component: RoutesAcl
        },
        {
          name: "Security::Routes::RoutesPermissions",
          path: "access/permissions/",
          meta: {
            label: "Permissions"
          },
          component: AclAccount
        }
      ]
    },
    {
      name: "Security::SmartStructures",
      path: "smartStructures",
      component: SmartStructures,
      meta: {
        label: "Smart Structures"
      },
      children: [
        {
          name: "Security::SmartStructures::name",
          path: ":ssName",
          component: SmartStructuresContent,
          meta: {
            label: ":ssName"
          },
          children: [
            {
              name: "Security::SmartStructures::Infos",
              path: "infos",
              meta: {
                label: "Infos"
              },
              component: SmartStructuresSections.Infos,
              props: true // Set ssName as a vue component prop
            },
            {
              name: "Security::SmartStructures::Structures",
              path: "structureProfile",
              meta: {
                label: "Structure Profile"
              },
              component: SmartStructuresSections.Structures,
              props: true // Set ssName as a vue component prop
            },
            {
              name: "Security::SmartStructures::Elements",
              path: "elementsProfile",
              meta: {
                label: "Element Profile"
              },
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
      meta: {
        label: "Profiles"
      },
      component: SmartElements
    },
    {
      name: "Security::Roles",
      path: "roles",
      meta: {
        label: "Roles"
      },
      component: Roles
    }
  ]
};
