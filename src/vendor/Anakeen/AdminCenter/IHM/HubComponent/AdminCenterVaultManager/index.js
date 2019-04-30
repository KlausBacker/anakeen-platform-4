import VaultManager from "./AdminCenterVaultManagerEntry";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub.AdminVaultManager
) {
  // @ts-ignore
  window.ank.hub.AdminVaultManager.resolve(
    VaultManager,
    "ank-admin-vault-manager"
  );
}
