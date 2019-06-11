import Breadcrumb from "./DevBreadcrumb.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-breadcrumb"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-breadcrumb"].resolve(
    Breadcrumb,
    "ank-dev-breadcrumb"
  );
}
