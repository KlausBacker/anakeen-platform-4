import TokenManager from "./AuthenticationTokensHubComponent";

export default function install(Vue) {
  Vue.component("ank-hub-authentication-tokens", TokenManager);
}
