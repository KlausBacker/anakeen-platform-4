import Workflow from "./Workflow.vue";
import WflContent from "./WorkflowContent.vue";
import Infos from "./Infos/Infos.vue";
import Steps from "./Steps/Steps.vue";
import Transitions from "./Transitions/Transitions.vue";
import { AnkSmartElement } from "@anakeen/ank-components";
import ProfileView from "../../components/profile/profile.vue";

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
          props: true,
          children: [
            {
              name: "Wfl::steps::pdoc",
              path: "pdoc/:seIdentifier",
              meta: {
                label: ":seIdentifier"
              },
              component: ProfileView,
              props: route => ({
                profileId: route.params.seIdentifier.toString()
              })
            }
          ]
        },
        {
          name: "Wfl::transitions",
          path: "transitions",
          component: Transitions,
          props: true,
          children: [
            {
              name: "Wfl::transitions::mail",
              path: "mail/:seIdentifier",
              meta: {
                label: ":seIdentifier"
              },
              component: AnkSmartElement,
              props: route => ({
                initid: route.params.seIdentifier.toString(),
                viewId: "!defaultConsultation"
              })
            },
            {
              name: "Wfl::transitions::timers::volatile",
              path: "timers/volatile/:seIdentifier",
              meta: {
                label: ":seIdentifier"
              },
              component: AnkSmartElement,
              props: route => ({
                initid: route.params.seIdentifier.toString(),
                viewId: "!defaultConsultation"
              })
            },
            {
              name: "Wfl::transitions::timers::persistent",
              path: "timers/persistent/:seIdentifier",
              meta: {
                label: ":seIdentifier"
              },
              component: AnkSmartElement,
              props: route => ({
                initid: route.params.seIdentifier.toString(),
                viewId: "!defaultConsultation"
              })
            },
            {
              name: "Wfl::transitions::timers::unattach",
              path: "timers/unattach/:seIdentifier",
              meta: {
                label: ":seIdentifier"
              },
              component: AnkSmartElement,
              props: route => ({
                initid: route.params.seIdentifier.toString(),
                viewId: "!defaultConsultation"
              })
            }
          ]
        },
        {
          name: "Wfl::permissions",
          path: "permissions",
          component: ProfileView,
          props: route => ({
            profileId: route.params.wflName,
            onlyExtendedAcls: true
          })
        }
      ]
    }
  ]
};
