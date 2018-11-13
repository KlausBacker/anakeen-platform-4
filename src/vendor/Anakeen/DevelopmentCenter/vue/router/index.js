import Main from "../DevCenter/Main.vue";
import ErrorNotFound from "../DevCenter/Error.vue";
import SectionsRoutes from "../sections/index";

export const routes = [
  {
    path: "/devel",
    name: "devCenter",
    component: Main,
    meta: {
      label: "Development Center"
    },
    children: SectionsRoutes
  },
  // Redirection
  {
    path: "*",
    component: ErrorNotFound,
    meta: {
      label: "Page not found"
    }
  }
];

export default {
  routes,
  mode: "history",
  saveScrollPosition: true
};
