import TrashManager from "./AdminCenterTrashManagerEntry.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-admin-trash-manager"]
) {
  // @ts-ignore
  window.ank.hub["ank-admin-trash-manager"].resolve(TrashManager, "ank-admin-trash-manager");
}
