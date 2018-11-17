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
      path: ":wflName",
      component: WflContent,
      meta: {
        label: ":wflName"
      },
      children: [
        {
          name: "Wfl::infos",
          path: "infos",
          component: Infos,
          props: true
        },
        {
          name: "Wfl::steps",
          path: "steps",
          component: Steps,
          props: true
        },
        {
          name: "Wfl::transitions",
          path: "transitions",
          component: Transitions,
          props: true
        },
        {
          name: "Wfl::permissions",
          path: "permissions",
          component: Permissions,
          props: true
        }
      ]
    }
  ]
};
