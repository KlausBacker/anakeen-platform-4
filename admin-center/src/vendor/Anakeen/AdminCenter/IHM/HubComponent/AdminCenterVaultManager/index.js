import VaultManager from "./AdminCenterVaultManagerEntry";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-admin-vault-manager"]
) {
  // @ts-ignore
  window.ank.hub["ank-admin-vault-manager"].resolve(VaultManager, "ank-admin-vault-manager");
}
