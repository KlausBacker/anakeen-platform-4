import $ from "jquery";
import loadCss from "fg-loadcss";
import littleLoader from "little-loader";
// eslint-disable-next-line no-unused-vars
import { IHubConfiguration, Assets } from "./HubConfiguration";

declare global {
  interface Window {
    ank: {
      hub: [];
    };
  }
}

class HubEntriesUtil {
  /**
   * The hub base url
   */
  private readonly baseFetch: string;
  /**
   * The hub vue instance reference
   * @type {String}
   */
  private readonly hubId: string;
  /**
   * HubEntries constructor.
   * @param {String} hubId logical name of the current hub
   * @param {string} baseFetch
   */
  constructor(hubId: string, baseFetch: string = "/hub/config") {
    this.hubId = hubId;
    this.baseFetch = baseFetch;
  }

  async initializeHub(): Promise<IHubConfiguration> {
    const config = await this.fetchConfiguration();
    return await this.loadConfiguration(config);
  }

  /**
   * Get the configuration of the hub and return it in a  promise
   */
  private async fetchConfiguration() {
    const response = await fetch(`${this.baseFetch}/${window.encodeURIComponent(this.hubId)}`, {
      credentials: "same-origin",
      headers: new Headers({
        accept: "application/json; text/html"
      })
    });
    if (!response.ok) {
      const responseText = await response.text();
      throw new Error(responseText);
    }
    const content = await response.json();
    if (!content.success) {
      throw content.message || content.exceptionMessage || "An error has occurred";
    }
    const configuration: IHubConfiguration = content.data;
    return configuration;
  }

  /**
   * Analyse the configuration, inject it and return an object of async vue element
   * @param configuration
   */
  private async loadConfiguration(configuration: IHubConfiguration) {
    window.ank = window.ank || {};
    window.ank.hub = window.ank.hub || {};
    await this.registerAsset(configuration.globalAssets);
    await configuration.hubElements.map(async currentElement => {
      if (
        currentElement.component &&
        currentElement.component.name &&
        currentElement.entryOptions &&
        !currentElement.component.internal
      ) {
        let hubElementOk, hubElementKo;
        // @ts-ignore
        if (!window.ank.hub[currentElement.component.name]) {
          const hubElementPromise = new Promise((resolve, reject) => {
            hubElementOk = vueComponent => {
              resolve(vueComponent);
            };
            hubElementKo = error => {
              reject(error);
            };
          });
          window.ank.hub[currentElement.component.name] = {
            promise: hubElementPromise,
            resolve: hubElementOk,
            reject: hubElementKo
          };
        }
        await this.registerAsset(currentElement.assets);
      }
    });
    return configuration;
  }

  /**
   * Register CSS and JS in the current page
   *
   * @param currentAsset
   */
  private registerAsset(currentAsset: Assets): Promise<unknown[]> {
    const assetsPromises: Array<Promise<unknown>> = [];
    if (currentAsset.js && currentAsset.js.length) {
      currentAsset.js.forEach(jsUrl => {
        if (jsUrl) {
          const insertElement = new Promise((resolve, reject) => {
            if ($(`script[src="${jsUrl}"]`).length === 0) {
              littleLoader(jsUrl, err => {
                if (err) {
                  return reject(err);
                }
                resolve();
              });
            } else {
              resolve();
            }
          });
          assetsPromises.push(insertElement);
        }
      });
    }
    if (currentAsset.css && currentAsset.css.length) {
      currentAsset.css.forEach(cssUrl => {
        if (cssUrl) {
          loadCss.loadCSS(cssUrl);
        }
      });
    }
    return Promise.all(assetsPromises);
  }
}

export default HubEntriesUtil;
