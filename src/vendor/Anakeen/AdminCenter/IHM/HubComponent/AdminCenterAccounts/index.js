import AccountManager from "./AdminCenterAccountsEntry.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub.AdminAccountManager
) {
  // @ts-ignore
  window.ank.hub.AdminAccountManager.resolve(
    AccountManager,
    "ank-admin-account"
  );
}
