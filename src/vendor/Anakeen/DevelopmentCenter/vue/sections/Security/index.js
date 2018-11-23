import Security from "./SecurityDevCenter";
import Routes from "../Security/Routes/RoutesDevCenter";
import Roles from "../Security/Role/RoleDevCenter";
import RoutesAcl from "../Security/Routes/RoutesAcl/RoutesAcl";
import AclAccount from "../Security/Routes/RoutesPermissions/RoutesPermissions";
import SmartStructures from "../Security/SmartStructures/SmartStructuresSecurity.vue";
import SmartStructuresContent from "../Security/SmartStructures/SmartStructuresSecurityContent.vue";
import SmartStructuresSections from "../Security/SmartStructures/subsections/export";

import Profiles from "../Security/Profiles/Profiles.vue";
import ProfileView from "../Security/Profiles/ProfileVisualizer/ProfileVisualizerContent.vue";

import Workflows from "../Security/Workflows/Workflows.vue";
import WorkflowsContent from "../Security/Workflows/WorkflowsContent.vue";
import WorkflowsRights from "../Security/Workflows/Rights/Rights.vue";
import WorkflowsAccesses from "../Security/Workflows/Accesses/Accesses.vue";
import ElementView from "../SmartElements/ElementView/ElementView.vue";

export default {
  label: "Security",
  name: "Security",
  path: "security",
  order: 3,
  component: Security,
  children: [
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
                label: "Default Element Profile"
              },
              component: SmartStructuresSections.Elements,
              props: true // Set ssName as a vue component prop
            },
            {
              name: "Security::SmartStructures::Fields",
              path: "fields",
              meta: {
                label: "Default Field Access"
              },
              component: SmartStructuresSections.Fields,
              props: true // Set ssName as a vue component prop
            }
          ]
        }
      ]
    },
    {
      name: "Security::Profiles",
      path: "profiles",
      meta: {
        label: "Profiles"
      },
      component: Profiles,
      children: [
        {
          name: "Security::Profile::Access::Element",
          path: ":seIdentifier",
          meta: {
            label: ":seIdentifier"
          },
          component: ProfileView,
          props: true
        }
      ]
    },
    {
      name: "Security::Roles",
      path: "roles",
      meta: {
        label: "Roles"
      },
      component: Roles,
      children: [
        {
          name: "Security::Roles::element",
          path: ":seIdentifier",
          meta: {
            label: ":seIdentifier"
          },
          component: ElementView,
          props: route => ({
            initid: route.params.seIdentifier.toString(),
            viewId: "!defaultConsultation",
            style: "width: 100%; height: 100%"
          })
        }
      ]
    },
    {
      name: "Security::Workflows",
      path: "workflows",
      meta: {
        label: "Workflows"
      },
      component: Workflows,
      children: [
        {
          name: "Security::Workflows::Content",
          path: ":workflowId",
          meta: {
            label: ":workflowId"
          },
          component: WorkflowsContent,
          children: [
            {
              name: "Security::Workflows::Rights",
              path: "rights",
              meta: {
                label: "Rights"
              },
              component: WorkflowsRights,
              props: true // Set ssName as a vue component prop
            },
            {
              name: "Security::Workflows::Access",
              path: "accesses",
              meta: {
                label: "Accesses"
              },
              component: WorkflowsAccesses,
              props: true // Set ssName as a vue component prop
            }
          ]
        }
      ]
    },
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
    }
  ]
};
