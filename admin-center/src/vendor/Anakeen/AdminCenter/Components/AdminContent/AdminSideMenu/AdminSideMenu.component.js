import { PLUGIN_SCHEMA } from "../../utils/plugins";
import { mapGetters } from 'vuex';

export default {
    mounted() {
        // this.$router.afterEach((to, from) => {
        //     const rootPath = (to.matched && to.matched.length) ? to.matched[0].path : to.path;
        //     const rootPlugin = this.plugins.find(p => p[PLUGIN_SCHEMA.pluginPath] === rootPath);
        //     if (rootPlugin) {
        //         this.enableSubMenu = (rootPlugin[PLUGIN_SCHEMA.subcomponents]
        //             && rootPlugin[PLUGIN_SCHEMA.subcomponents].length);
        //         this.$nextTick(() => {
        //             if (this.enableSubMenu) {
        //                 const autoSelectPlugin = rootPlugin[PLUGIN_SCHEMA.subcomponents]
        //                     .find(sub => sub[PLUGIN_SCHEMA.autoselect]);
        //                 if (autoSelectPlugin) {
        //                     this.$router.replace({path: autoSelectPlugin[PLUGIN_SCHEMA.pluginPath]});
        //                 }
        //             }
        //         });
        //     }
        // });
    },
    computed: {
        ...mapGetters({
            rootPlugin: 'getRootPlugin',
            plugins: 'getPluginsList',
        }),

        pluginSchema() {
            return PLUGIN_SCHEMA;
        },

        enableSubMenu() {
            return this.rootPlugin && this.rootPlugin[PLUGIN_SCHEMA.subcomponents]
                && this.rootPlugin[PLUGIN_SCHEMA.subcomponents].length;
        }
    }

};