import { PLUGIN_SCHEMA } from "../../utils/plugins";

export default {
    props: {
        plugins: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            enableSubMenu: false,
        };
    },
    mounted() {
        this.$router.afterEach((to, from) => {
            const rootPath = (to.matched && to.matched.length) ? to.matched[0].path : to.path;
            const rootPlugin = this.plugins.find(p => p[PLUGIN_SCHEMA.pluginPath] === rootPath);
            if (rootPlugin) {
                this.enableSubMenu = (rootPlugin[PLUGIN_SCHEMA.subcomponents]
                    && rootPlugin[PLUGIN_SCHEMA.subcomponents].length);
            }
        });
    },
    computed: {
        currentPlugin() {
            return this.plugins.find(p => p[PLUGIN_SCHEMA.pluginPath] === this.$router.currentRoute.path);
        },

        pluginSchema() {
            return PLUGIN_SCHEMA;
        }
    }

};