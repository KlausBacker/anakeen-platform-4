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