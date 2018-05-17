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
            const rootPlugin = this.plugins.find(p => p.pluginPath === rootPath);
            if (rootPlugin) {
                this.enableSubMenu = (rootPlugin.subcomponents && rootPlugin.subcomponents.length);
            }
        });
    },
    computed: {
        currentPlugin() {
            return this.plugins.find(p => p.pluginPath === this.$router.currentRoute.path);
        },
    }

};