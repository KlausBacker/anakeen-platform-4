// import Vue from "vue";
//
// /**
//  * Return an Async Vue component configuration
//  * @param componentPath - path of the component
//  * @return {function(): Promise<T | never>} A function that returns a Promise resolving on the requested module
//  */
// const importAsyncComponent = (componentPath) => () => import(/* webpackChunkName: "anakeen-component-[index]" */`${componentPath}`).then((module) => {
//   Vue.use(module.default);
//   return module;
// }).catch(err => {console.error(err); throw err;});

// Export asynchronous components
// export const AnkLoading = importAsyncComponent("./AnakeenLoading");
// export const AnkLogout = importAsyncComponent("./Logout");
// export const AnkIdentity = importAsyncComponent("./Identity");
// export const AnkAuthent = importAsyncComponent("./Authent");
// export const AnkAuthentPassword = importAsyncComponent("./Authent/AuthentPassword");
// export const AnkSmartElement = importAsyncComponent("./SmartElement");
// export const AnkSEList = importAsyncComponent("./SEList");
// export const AnkSETabs = importAsyncComponent("./SETabs");
// export const AnkNotifier = importAsyncComponent("./Notifier");
// export const AnkDockTab = importAsyncComponent("./Dock/DockTab");
// export const AnkDock = importAsyncComponent("./Dock");
// export const AnkSEGrid = importAsyncComponent("./Grid");
//

import Loading from "./AnakeenLoading";
import Logout from "./Logout";
import Identity from "./Identity";
import Authent from "./Authent";
import AuthentPassword from "./Authent/AuthentPassword";
import SmartElement from "./SmartElement";
import SEList from "./SEList";
import SETabs from "./SETabs";
import Notifier from "./Notifier";
import DockTab from "./Dock/DockTab";
import Dock from "./Dock";
import SEGrid from "./Grid";

export const AnkLoading = Loading;
export const AnkLogout = Logout;
export const AnkIdentity = Identity;
export const AnkAuthent = Authent;
export const AnkAuthentPassword = AuthentPassword;
export const AnkSmartElement = SmartElement;
export const AnkSEList = SEList;
export const AnkSETabs = SETabs;
export const AnkNotifier = Notifier;
export const AnkDockTab = DockTab;
export const AnkDock = Dock;
export const AnkSEGrid = SEGrid;
