import Main from "../DevCenter/Main.vue";
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
    redirect: {
      name: "devCenter"
    }
  }
];

export default {
  routes,
  mode: "history",
  saveScrollPosition: true
};
