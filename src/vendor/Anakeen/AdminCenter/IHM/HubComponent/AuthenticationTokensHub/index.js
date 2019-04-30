import TokenManager from "./AuthenticationTokensHubComponent";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub.AdminTokenManager
) {
  // @ts-ignore
  window.ank.hub.AdminTokenManager.resolve(
    TokenManager,
    "ank-hub-authentication-tokens"
  );
}
