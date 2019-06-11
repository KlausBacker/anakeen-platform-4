import AccountManager from "./AdminCenterAccountsEntry.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-admin-account"]
) {
  // @ts-ignore
  window.ank.hub["ank-admin-account"].resolve(
    AccountManager,
    "ank-admin-account"
  );
}
