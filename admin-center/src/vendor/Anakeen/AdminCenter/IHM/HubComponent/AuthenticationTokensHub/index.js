import TokenManager from "./AuthenticationTokensHubComponent";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-hub-authentication-tokens"]
) {
  // @ts-ignore
  window.ank.hub["ank-hub-authentication-tokens"].resolve(TokenManager, "ank-hub-authentication-tokens");
}
