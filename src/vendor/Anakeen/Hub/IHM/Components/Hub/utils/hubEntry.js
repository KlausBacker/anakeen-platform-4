import Vue from "vue";

class HubEntries {
  /**
   * HubEntries constructor.
   * @param {Object} hubInstance
   * @param {Array} contents
   * @param {string} baseUrl
   */
  constructor(hubInstance, contents = [], baseUrl = "/hub/station") {
    /**
     * The hub vue instance reference
     * @type {Object}
     */
    this.hubInstance = hubInstance;

    /**
     * The hub entries contents
     * @type {Array}
     */
    this.contents = contents;

    /**
     * The hub base url
     * @type {String}
     */
    this.baseUrl = baseUrl;
  }

  loadAssets() {
    return Promise.all(
      this.contents.map(dockContent => {
        if (dockContent.entryOptions && dockContent.entryOptions.libName) {
          window.ank = window.ank || {};
          window.ank.hub = window.ank.hub || {};
          if (!window.ank.hub[dockContent.entryOptions.libName]) {
            let hubElementOk, hubElementKo;
            const hubElementPromise = new Promise((resolve, reject) => {
              hubElementOk = (
                vueComponent,
                name = dockContent.entryOptions.libName
              ) => {
                resolve({
                  name,
                  component: vueComponent
                });
              };
              hubElementKo = error => {
                reject(error);
              };
            });
            window.ank.hub[dockContent.entryOptions.libName] = {
              promise: hubElementPromise,
              resolve: hubElementOk,
              reject: hubElementKo
            };
          }
        }
        if (dockContent.assets) {
          const assets = dockContent.assets;
          const assetsPromises = [];
          if (assets.js && assets.js.length) {
            assets.js.forEach(jsUrl => {
              if (jsUrl) {
                assetsPromises.push(
                  this.hubInstance.$loader({
                    url: jsUrl,
                    library: dockContent.entryOptions
                      ? dockContent.entryOptions.libName
                      : undefined
                  })
                );
              }
            });
          }
          if (assets.css && assets.css.length) {
            assets.css.forEach(cssUrl => {
              if (cssUrl) {
                this.hubInstance.$loadCssFile(cssUrl);
              }
            });
          }
          return Promise.all(assetsPromises).then(() => {
            return dockContent;
          });
        } else {
          return Promise.resolve(dockContent);
        }
      })
    );
  }

  useComponents() {
    if (window && window.ank && window.ank.hub) {
      return Promise.all(
        Object.values(window.ank.hub).map(
          currentElement => currentElement.promise
        )
      ).then(hubElement => {
        hubElement.map(currentElement => {
          Vue.component(currentElement.name, currentElement.component);
        });
      });
    }
    return Promise.resolve();
  }
}

export default HubEntries;
