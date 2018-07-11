import { PLUGIN_SCHEMA } from "../../../../utils/plugins";

export default {
  getPluginsList: state => {
    return state.plugins;
  },
  getRootPlugin: state => {
    return state.rootPlugin;
  },
  getPluginByName: state => name => {
    const findCb = p => p[PLUGIN_SCHEMA.name] === name;
    let plugin = null;
    let i = 0;
    while (i < state.plugins.length && !plugin) {
      const current = state.plugins[i];
      if (findCb(current)) {
        plugin = current;
      } else if (
        current[PLUGIN_SCHEMA.subcomponents] &&
        current[PLUGIN_SCHEMA.subcomponents].length
      ) {
        const findPlugin = current[PLUGIN_SCHEMA.subcomponents].find(findCb);
        if (findPlugin) {
          plugin = findPlugin;
        }
      }
      i++;
    }
    return plugin;
  },
  getPluginsFromPath: (state, getters) => path => {
    const plugins = [];
    const findCb = plugin => {
      const regex = new RegExp(plugin[PLUGIN_SCHEMA.pluginPath]);
      if (regex.test(path)) {
        plugins.push(plugin);
      }
    };
    getters.getPluginsList.forEach(plugin => {
      findCb(plugin);
      if (plugin[PLUGIN_SCHEMA.subcomponents]) {
        plugin[PLUGIN_SCHEMA.subcomponents].forEach(findCb);
      }
    });
    return plugins;
  }
};
