import Ui from "./Ui.vue";
import UiContent from "./UiContent.vue";
import Infos from "./Infos/Infos.vue";
import ViewsConf from "./ViewsConfiguration/ViewsConf.vue";
import ControlConf from "./ControlConfiguration/ControlConf.vue";
import Masks from "./Masks/Masks.vue";

export default {
  name: "Ui",
  path: "ui",
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
          path: "control/",
          component: ControlConf,
          props: true
        },
        {
          name: "Ui::masks",
          path: "masks/",
          component: Masks,
          props: true
        }
      ]
    }
  ]
};
