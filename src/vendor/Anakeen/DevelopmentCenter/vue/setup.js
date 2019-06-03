const LINK_ROUTER_ROLE = "develRouterLink";

export function setupVue(vueHubElement) {
  const store = vueHubElement.getStore();
  if (store && store.registerModule && !store.state.devCenter) {
    const moduleStore = require("./store/index.js").default;
    store.registerModule("devCenter", moduleStore);
  }
}

/**
 * Intercept DOM links and use the router to navigate. Prevent the page reloading.
 * The link must be marked with data-role="develRouterLink"
 * @param router - Vue router instance
 * @param element - parent DOM element
 */
export const interceptDOMLinks = (element, cb = () => {}) => {
  const $element = kendo.jQuery(element);
  $element.on("click", `[data-role=${LINK_ROUTER_ROLE}]`, event => {
    event.preventDefault();
    event.stopPropagation();
    const link = event.currentTarget;
    if (kendo.jQuery(link).is("a")) {
      const path = link.pathname + (link.search || "");
      if (cb && typeof cb === "function") {
        cb(path);
      }
    } else {
      console.warn(link.outerHTML, "is not a link (<a>)");
    }
  });
};

export function syncRouter() {};

// export function syncRouter(vueHubElement) {
//   const router = vueHubElement.getRouter();
//   const store = vueHubElement.getStore();
//   if (router && store) {
//     store.subscribe((mutation, state) => {
//       const regex = /(\w+)\/SET_ROUTE/;
//       const matches = mutation.type.match(regex);
//       if (matches && matches.length > 1) {
//         const namespace = matches[1];
//         const localStateRoute = state[namespace].currentRoute;
//         router.navigate(
//           `/${namespace}/` +
//             localStateRoute.map(r => r.url || r.name || r).join("/")
//         );
//         const NS = namespace.replace(
//           /([A-Z]?[a-z]+)([A-Z][a-z]+)/g,
//           (correspondance, ...args) => {
//             const tokens = args.slice(0, args.length - 2);
//             if (tokens && tokens.length) {
//               return tokens
//                 .map(t => t.charAt(0).toUpperCase() + t.slice(1))
//                 .join(" ");
//             } else {
//               return args[args.length - 1];
//             }
//           }
//         );
//         store.commit("SET_CURRENT_ROUTE", [NS, ...localStateRoute]);
//       }
//     });
//   }
// }
