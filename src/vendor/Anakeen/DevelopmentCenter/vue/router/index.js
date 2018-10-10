import Main from "../DevCenter/Main.vue";
import SectionsRoutes from "../sections/index";

export const routes = [
  {
    path: "/devel",
    name: "devCenter",
    component: Main,
    children: SectionsRoutes
  },
  // Redirection
  {
    path: "*",
    redirect: "/devel"
  }
];

export default {
  routes,
  mode: "history",
  saveScrollPosition: true
};
