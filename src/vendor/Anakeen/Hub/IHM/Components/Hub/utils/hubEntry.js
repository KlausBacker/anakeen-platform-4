import Vue from "vue";

class HubEntries {
  /**
   * HubEntries constructor.
   * @param {Object} hubInstance
   * @param {Array} contents
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
        if (
          dockContent.assets &&
          dockContent.assets.js &&
          dockContent.assets.js.length
        ) {
          const assets = dockContent.assets;
          const assetsPromises = [];
          if (assets.js && assets.js.length) {
            assets.js.forEach(jsUrl => {
              assetsPromises.push(
                this.hubInstance.$loader({
                  url: jsUrl,
                  library: dockContent.tab.module.name
                })
              );
            });
          }
          if (assets.css && assets.css.length) {
            assets.css.forEach(cssUrl => {
              this.hubInstance.$loadCssFile(cssUrl);
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

  loadEntries() {
    return Promise.all(
      this.contents.map(dockContent => {
        let moduleName;
        let componentTemplate;
        let component;
        let componentChildRoutes = [];

        if (dockContent && dockContent.tab && dockContent.tab.module) {
          // Use js module (that defines the vue component)
          if (dockContent.tab.module.name) {
            moduleName = dockContent.tab.module.name;
            // Use the vue plugin available in global space
            if (global[moduleName]) {
              Vue.use(global[moduleName].default);
            }
          }

          // Retrieve the vue instance component and prepare the template
          if (
            dockContent.tab.module.component &&
            dockContent.tab.module.component.componentName
          ) {
            // Add entryPoint in template to give the hub entry path to the component
            componentTemplate = `<${
              dockContent.tab.module.component.componentName
            } ref="${
              dockContent.tab.module.component.componentName
            }" v-bind="componentProps" :hubEntryPoint="hubEntryPoint"></${
              dockContent.tab.module.component.componentName
            }>`;
            component = Vue.component(
              dockContent.tab.module.component.componentName
            );
            if (component) {
              // Get optional component children routes
              if (component.options && component.options.componentSubRoutes) {
                componentChildRoutes = component.options.componentSubRoutes;
              }
            }
            if (this.hubInstance.$router) {
              const routerEntry = dockContent.tab.module.router.entry;
              const _that = this;
              this.hubInstance.$router.addRoutes([
                {
                  path: `${this.baseUrl}/${routerEntry}`,
                  component: () =>
                    new Promise(resolve => {
                      resolve({
                        template: componentTemplate,
                        data() {
                          return {
                            componentProps:
                              dockContent.tab.module.component.props || {},
                            hubEntryPoint: `${_that.baseUrl}/${routerEntry}`
                          };
                        }
                      });
                    }),
                  children: componentChildRoutes
                }
              ]);
            }
          }
        }
        return Promise.resolve(dockContent);
      })
    );
  }
}

export default HubEntries;
