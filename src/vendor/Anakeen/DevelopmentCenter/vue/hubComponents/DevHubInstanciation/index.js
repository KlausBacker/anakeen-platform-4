import HubInstanciation from "./DevHubInstanciation.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-hub-instanciation"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-hub-instanciation"].resolve(
    HubInstanciation,
    "ank-dev-hub-instanciation"
  );
}
