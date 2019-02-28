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
    this.contents.map(dockContent => {
      let libName;
      if (dockContent && dockContent.entryOptions && dockContent.component) {
        // Use js module (that defines the vue component)
        if (dockContent.entryOptions.libName) {
          libName = dockContent.entryOptions.libName;
          // Use the vue plugin available in global space
          const lib = window[libName] || global[libName];
          if (lib) {
            Vue.use(lib);
          }
        }
      }
    });
  }
}

export default HubEntries;
