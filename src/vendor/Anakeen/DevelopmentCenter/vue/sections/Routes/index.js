import Routes from "./Routes/Routes.vue";
import Middleware from "./Middlewares/Middlewares.vue";
import RoutesParent from "./RoutesParent.vue";

export default {
  name: "Routes",
  path: "routes",
  order: 6,
  meta: {
    label: "Routes"
  },
  component: RoutesParent,
  children: [
    {
      name: "routes",
      path: "routes",
      component: Routes
    },
    {
      name: "middlewares",
      path: "middlewares",
      component: Middleware
    }
  ]
};
