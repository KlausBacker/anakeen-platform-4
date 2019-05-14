const loadCss = require("fg-loadcss");
const littleLoader = require("little-loader");

class HubEntriesUtil {
  /**
   * HubEntries constructor.
   * @param {String} hubId
   * @param {string} baseFetch
   */
  constructor(hubId, baseFetch = "/hub/config") {
    /**
     * The hub vue instance reference
     * @type {String}
     */
    this.hubId = hubId;

    /**
     * The hub base url
     * @type {String}
     */
    this.baseFetch = baseFetch;
  }

  fetchConfiguration() {
    const currentConf = this;
    return fetch(`${this.baseFetch}/${window.encodeURIComponent(this.hubId)}`, {
      credentials: "same-origin",
      headers: new Headers({
        accept: "application/json; text/html"
      })
    })
      .then(response => {
        if (
          !response.ok &&
          response.headers.get("Content-Type").indexOf("text/html") > -1
        ) {
          return response.text().then(htmlContent => {
            document.write(htmlContent);
            let errorMsg = `${response.status}`;
            if (response.statusText) {
              errorMsg += ` (${response.statusText})`;
            }
            throw new Error(errorMsg);
          });
        } else {
          return response.json();
        }
      })
      .then(response => {
        if (response.success) {
          const data = response.data;
          this.data = data;
          const globalAssets = data.globalAssets || [];
          currentConf.contents = [{ assets: globalAssets }].concat(
            data.hubElements
          );
        } else {
          throw response.message ||
            response.exceptionMessage ||
            "An error has occurred";
        }
      })
      .catch(error => {
        console.error(error);
        throw error;
      });
  }

  loadAssets() {
    return Promise.all(
      this.contents.map(dockContent => {
        if (
          dockContent.component &&
          dockContent.component.name &&
          dockContent.entryOptions &&
          !dockContent.component.internal
        ) {
          window.ank = window.ank || {};
          window.ank.hub = window.ank.hub || {};
          if (!window.ank.hub[dockContent.component.name]) {
            let hubElementOk, hubElementKo;
            const hubElementPromise = new Promise((resolve, reject) => {
              hubElementOk = vueComponent => {
                resolve(vueComponent);
              };
              hubElementKo = error => {
                reject(error);
              };
            });
            window.ank.hub[dockContent.component.name] = {
              promise: hubElementPromise,
              resolve: hubElementOk,
              reject: hubElementKo,
              timeout: dockContent.entryOptions.loadingTimeout
            };
          }
        }
        if (dockContent.assets) {
          const assets = dockContent.assets;
          const assetsPromises = [];
          if (assets.js && assets.js.length) {
            assets.js.forEach(jsUrl => {
              if (jsUrl) {
                const insertElement = new Promise((resolve, reject) => {
                  littleLoader(jsUrl, err => {
                    if (err) {
                      return reject(err);
                    }
                    resolve();
                  });
                });
                assetsPromises.push(insertElement);
              }
            });
          }
          if (assets.css && assets.css.length) {
            assets.css.forEach(cssUrl => {
              if (cssUrl) {
                loadCss.loadCSS(cssUrl);
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
}

export default HubEntriesUtil;
