import Ui from "./Ui.vue";
import UiContent from "./UiContent.vue";
import Infos from "./Infos/Infos.vue";
import ViewsConf from "./ViewsConfiguration/ViewsConf.vue";
import ControlConf from "./ControlConfiguration/ControlConf.vue";
import Masks from "./Masks/Masks.vue";
import ElementView from "../SmartElements/ElementView/ElementView.vue";
import ProfileView from "../../components/profile/profile.vue";

export default {
  name: "Ui",
  path: "ui",
  order: 2,
  meta: {
    label: "User Interface"
  },
  component: Ui,
  children: [
    {
      name: "Ui::name",
      path: ":ssName",
      component: UiContent,
      meta: {
        label: ":ssName"
      },
      children: [
        {
          name: "Ui::infos",
          path: "infos/",
          component: Infos,
          props: true
        },
        {
          name: "Ui::views",
          path: "views/",
          component: ViewsConf,
          props: true
        },
        {
          name: "Ui::control",
          path: "control",
          component: ControlConf,
          props: true,
          children: [
            {
              name: "Ui::control::element",
              path: "element/:seIdentifier",
              meta: {
                label: ":seIdentifier"
              },
              component: ElementView,
              props: route => ({
                initid: route.params.seIdentifier.toString(),
                viewId: "!defaultConsultation"
              })
            },
            {
              name: "Ui::control::permissions",
              path: "permissions/:seIdentifier",
              meta: {
                label: ":seIdentifier"
              },
              component: ProfileView,
              props: route => ({
                profileId: route.params.seIdentifier.toString(),
                detachable: true
              })
            }
          ]
        },
        {
          name: "Ui::masks",
          path: "masks",
          component: Masks,
          props: true,
          children: [
            {
              name: "Ui::masks::element",
              path: ":seIdentifier",
              meta: {
                label: ":seIdentifier"
              },
              component: ElementView,
              props: route => ({
                initid: route.params.seIdentifier.toString(),
                viewId: "!defaultConsultation"
              })
            }
          ]
        }
      ]
    }
  ]
};