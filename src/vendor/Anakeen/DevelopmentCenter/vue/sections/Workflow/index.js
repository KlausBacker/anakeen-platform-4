import Workflow from "./Workflow.vue";
import WflContent from "./WorkflowContent.vue";
import Infos from "./Infos/Infos.vue";
import Steps from "./Steps/Steps.vue";
import Transitions from "./Transitions/Transitions.vue";
import Permissions from "./Permissions/Permissions.vue";

export default {
  name: "Workflow",
  path: "wfl",
  order: 5,
  meta: {
    label: "Workflow"
  },
  component: Workflow,
  children: [
    {
      name: "Wfl::name",
      path: ":ssName",
      component: WflContent,
      meta: {
        label: ":ssName"
      },
      children: [
        {
          name: "Wfl::infos",
          path: "infos/:wflIdentifier",
          meta: {
            label: ":wflIdentifier"
          },
          component: Infos,
          props: true
        },
        {
          name: "Wfl::steps",
          path: "steps/:wflIdentifier",
          meta: {
            label: ":wflIdentifier"
          },
          component: Steps,
          props: true
        },
        {
          name: "Wfl::transitions",
          path: "transitions/:wflIdentifier",
          meta: {
            label: ":wflIdentifier"
          },
          component: Transitions,
          props: true
        },
        {
          name: "Wfl::permissions",
          path: "permissions/:wflIdentifier",
          meta: {
            label: ":wflIdentifier"
          },
          component: Permissions,
          props: true
        }
      ]
    }
  ]
};
