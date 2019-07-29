const LINK_ROUTER_ROLE = "adminRouterLink";

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
